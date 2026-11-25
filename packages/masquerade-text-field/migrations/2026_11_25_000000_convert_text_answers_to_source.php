<?php

/*
 * This file is part of flaiming/flarum-masquerade-text-field.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Builder;
use s9e\TextFormatter\Bundles\Fatdown;
use s9e\TextFormatter\Unparser;

/*
 * The original fork of FoF Masquerade (flaiming/masquerade, branch
 * text-field-1.0) stored "text" answers as s9e XML (Fatdown bundle). This
 * extension stores the source text instead and renders it on read, so convert
 * the existing rows, and give existing text fields the validation the
 * extension enforces from now on.
 */
// Rows written by the fork are s9e XML: <t>…</t> or <r …>…</r> (the root
// carries namespace declarations when HTML elements were used).
$isXml = fn (string $content): bool => (bool) preg_match('#^<([rt])(?:\s[^>]*)?>.*</\1>$#s', $content);

return [
    'up' => function (Builder $schema) use ($isXml) {
        $db = $schema->getConnection();
        $fieldIds = $db->table('fof_masquerade_fields')->where('type', 'text')->pluck('id');

        $db->table('fof_masquerade_fields')
            ->whereIn('id', $fieldIds)
            ->update(['validation' => 'string|max:10000']);

        $db->table('fof_masquerade_answers')
            ->whereIn('field_id', $fieldIds)
            ->whereNotNull('content')
            ->orderBy('id')
            ->chunkById(100, function ($answers) use ($db, $isXml) {
                foreach ($answers as $answer) {
                    if (! $isXml($answer->content)) {
                        continue;
                    }

                    $db->table('fof_masquerade_answers')
                        ->where('id', $answer->id)
                        ->update(['content' => Unparser::unparse($answer->content)]);
                }
            });
    },

    'down' => function (Builder $schema) use ($isXml) {
        $db = $schema->getConnection();
        $fieldIds = $db->table('fof_masquerade_fields')->where('type', 'text')->pluck('id');

        $db->table('fof_masquerade_fields')
            ->whereIn('id', $fieldIds)
            ->where('validation', 'string|max:10000')
            ->update(['validation' => null]);

        // The fork's format, without MediaEmbed: it can fetch remote URLs while parsing.
        $parser = Fatdown::getParser();
        $parser->disablePlugin('MediaEmbed');

        $db->table('fof_masquerade_answers')
            ->whereIn('field_id', $fieldIds)
            ->whereNotNull('content')
            ->where('content', '<>', '')
            ->orderBy('id')
            ->chunkById(100, function ($answers) use ($db, $isXml, $parser) {
                foreach ($answers as $answer) {
                    if ($isXml($answer->content)) {
                        continue;
                    }

                    $db->table('fof_masquerade_answers')
                        ->where('id', $answer->id)
                        ->update(['content' => $parser->parse($answer->content)]);
                }
            });
    },
];
