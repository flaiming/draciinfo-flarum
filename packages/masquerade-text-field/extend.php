<?php

/*
 * This file is part of flaiming/flarum-masquerade-text-field.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaiming\MasqueradeTextField;

use Flarum\Api\Schema;
use Flarum\Extend;
use FoF\Masquerade\Answer;
use FoF\Masquerade\Api\Resource\AnswerResource;
use FoF\Masquerade\Api\Resource\FieldResource;
use FoF\Masquerade\Field;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    // Masquerade validates the field type against its own hard-coded list.
    (new Extend\ApiResource(FieldResource::class))
        ->field('type', fn (Schema\Str $field) => AllowTextType::apply($field)),

    // Text fields always get the same validation: the admin modal only shows
    // the validation input for untyped fields, so a rule left over from a
    // previous type (e.g. "in:a,b") would otherwise silently stay.
    (new Extend\Event())
        ->listen('eloquent.saving: '.Field::class, function (Field $field) {
            if ($field->type === TextFieldRenderer::TYPE) {
                $field->validation = TextFieldRenderer::VALIDATION;
            }
        }),

    // The source text stays in "content" (the profile editor needs it);
    // the rendered HTML is exposed separately for display.
    (new Extend\ApiResource(AnswerResource::class))
        ->fields(fn () => [
            Schema\Str::make('contentHtml')
                ->nullable()
                ->get(fn (Answer $answer) => TextFieldRenderer::isTextAnswer($answer)
                    ? resolve(TextFieldRenderer::class)->render($answer->content)
                    : null),
        ]),
];
