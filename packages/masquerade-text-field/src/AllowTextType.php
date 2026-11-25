<?php

/*
 * This file is part of flaiming/flarum-masquerade-text-field.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaiming\MasqueradeTextField;

use Flarum\Api\Schema;

/**
 * Masquerade restricts the field "type" attribute with an `in:` rule built
 * from its own type list. Rewrite only that rule to also accept "text",
 * keeping any other rule (and its condition) as it is.
 */
class AllowTextType
{
    public static function apply(Schema\Str $field): Schema\Str
    {
        $rules = $field->getRules();

        // Clear all rules (third argument: override), then re-add each one.
        $field->rules([], true, true);

        foreach ($rules as ['rule' => $rule, 'condition' => $condition]) {
            if (is_string($rule) && str_starts_with($rule, 'in:')) {
                $types = explode(',', substr($rule, 3));

                if (! in_array(TextFieldRenderer::TYPE, $types, true)) {
                    $types[] = TextFieldRenderer::TYPE;
                }

                $rule = 'in:'.implode(',', $types);
            }

            // rules() rather than rule(): it also accepts rule objects (Rule::in() etc.).
            $field->rules([$rule], $condition, false);
        }

        return $field;
    }
}
