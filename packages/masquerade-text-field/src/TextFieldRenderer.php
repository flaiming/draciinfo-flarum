<?php

/*
 * This file is part of flaiming/flarum-masquerade-text-field.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaiming\MasqueradeTextField;

use FoF\Masquerade\Answer;
use Illuminate\Contracts\Cache\Repository as Cache;
use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Parser;
use s9e\TextFormatter\Renderer;

/**
 * Formats answers of the "text" field type.
 *
 * Answers are stored as the user's source text (Markdown) and rendered on
 * read. The formatter is a restricted variant of the s9e Fatdown bundle the
 * original fork used: the same Markdown features, but no media embeds (which
 * would fetch remote URLs while parsing) and only attribute-less inline HTML
 * (so users cannot apply the forum's CSS classes). Everything else is escaped,
 * so the output is safe to insert as HTML.
 */
class TextFieldRenderer
{
    public const TYPE = 'text';

    /** Validation applied to every text field (the admin UI has no input for it). */
    public const VALIDATION = 'string|max:10000';

    /** Bump when the configuration below changes, to rebuild the cached formatter. */
    private const CACHE_KEY = 'flaiming-masquerade-text-field.formatter.v2';

    private const ALLOWED_HTML = ['b', 'i', 'u', 's', 'strong', 'em', 'del', 'ins', 'mark', 'small', 'sub', 'sup', 'br', 'hr'];

    /** Shared by all instances: the container hands out a new one per resolve(). */
    private static ?Parser $parser = null;

    private static ?Renderer $renderer = null;

    public function __construct(private readonly Cache $cache)
    {
    }

    public static function isTextAnswer(Answer $answer): bool
    {
        return $answer->field?->type === self::TYPE;
    }

    public function render(?string $source): string
    {
        if ($source === null || trim($source) === '') {
            return '';
        }

        $this->boot();

        return self::$renderer->render(self::$parser->parse($source));
    }

    private function boot(): void
    {
        if (self::$parser) {
            return;
        }

        $components = $this->cache->rememberForever(self::CACHE_KEY, fn () => $this->configure()->finalize());

        self::$parser = $components['parser'];
        self::$renderer = $components['renderer'];
    }

    private function configure(): Configurator
    {
        $configurator = new Configurator();

        // Keep single line breaks (poems, lists of lines) as <br>.
        $configurator->rootRules->enableAutoLineBreaks();

        $configurator->Escaper;
        $configurator->Autoemail;
        $configurator->Autolink;
        $configurator->FancyPants;
        $configurator->HTMLEntities;
        $configurator->Litedown;
        $configurator->PipeTables;

        foreach (self::ALLOWED_HTML as $element) {
            $configurator->HTMLElements->allowElement($element);
        }

        // User-generated links, treated like external links in posts.
        $configurator->tags['URL']->template = '<a href="{@url}" rel="ugc nofollow noopener"><xsl:apply-templates/></a>';

        return $configurator;
    }
}
