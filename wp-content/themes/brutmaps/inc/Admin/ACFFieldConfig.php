<?php

namespace Brut\Admin;

class ACFFieldConfig
{

    public function tab(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $label,
            'type' => 'tab',
            'placement' => 'left',
        ];
    }

    public function googleMap(string $key, string $label, string $name, bool $required = false): array
    {
        return compact('key', 'label', 'name') + [
                'type' => 'google_map',
                'required' => $required ? 1 : 0,
            ];
    }

    public function wysiwyg(string $key, string $label, string $name): array
    {
        return compact('key', 'label', 'name') + [
                'type' => 'wysiwyg',
                'required' => 0,
            ];
    }

    public function number(string $key, string $label, string $name, string $placeholder = ''): array
    {
        return compact('key', 'label', 'name') + [
                'type' => 'number',
                'required' => 0,
                'placeholder' => $placeholder,
            ];
    }

    public function postObject(
        string $key,
        string $label,
        string $name,
        string $postType,
        bool   $multiple = false,
        string $width = '',
        string $instructions = ''
    ): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'post_object',
            'post_type' => [$postType],
            'return_format' => 'id',
            'required' => 0,
            'multiple' => $multiple,
            'instructions' => $instructions,
            'wrapper' => ['width' => $width],
        ];
    }

    public function image(string $key, string $label, string $name, bool $required = false, string $width = ''): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'image',
            'return_format' => 'array',
            'required' => $required ? 1 : 0,
            'wrapper' => ['width' => $width],
        ];
    }

    public function text(string $key, string $label, string $name): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'text',
            'required' => 0,
        ];
    }

    public function textarea(string $key, string $label, string $name): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'textarea',
            'required' => 0,
        ];
    }

    public function repeater(string $key, string $label, string $name, array $subFields): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'name' => $name,
            'type' => 'repeater',
            'required' => 0,
            'sub_fields' => $subFields,
        ];
    }
}
