<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\ProductType;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Language;
use Lunar\Models\TaxClass;

class LunarProductSeeder extends Seeder
{
    public function run()
    {
        /**
         * 1. Валюта
         */
        $currency = Currency::first() ?? Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'decimal_places' => 2,
            'exchange_rate' => 1,
            'enabled' => true,
            'default' => true,
        ]);

        /**
         * 2. ProductType
         */
        $type = ProductType::first() ?? ProductType::create([
            'name' => 'Default Product Type',
        ]);

        /**
         * 3. AttributeGroup (обязательные поля)
         */
        $group = AttributeGroup::first() ?? AttributeGroup::create([
            'name' => 'Product Main Data',
            'handle' => 'product_main',
            'position' => 1,
            'attributable_type' => \Lunar\Models\Product::class,
        ]);

        /**
         * 4. Атрибуты (все обязательные поля)
         */
        $attributes = [
            [
                'handle' => 'name',
                'name' => 'Name',
                'type' => \Lunar\FieldTypes\TranslatedText::class,
                'position' => 1,
            ],
            [
                'handle' => 'description',
                'name' => 'Description',
                'type' => \Lunar\FieldTypes\TranslatedText::class,
                'position' => 2,
            ],
            [
                'handle' => 'release_date',
                'name' => 'Release Date',
                'type' => \Lunar\FieldTypes\Text::class,
                'position' => 3,
            ],
        ];

        foreach ($attributes as $attr) {
            $attribute = Attribute::firstOrCreate(
                ['handle' => $attr['handle']],
                [
                    'name' => $attr['name'],
                    'type' => $attr['type'],
                    'attribute_type' => 'product',
                    'attribute_group_id' => $group->id,
                    'position' => $attr['position'],
                    'required' => false,
                    'configuration' => [],
                    'system' => false,   // ← ⚡ ДОБАВЛЕНО
                ]
            );

            $type->mappedAttributes()->syncWithoutDetaching([$attribute->id]);
        }

        $language = Language::first() ?? Language::create([
            'code' => 'en',
            'name' => 'English',
            'default' => true,
        ]);

        /**
         * 5. Product
         */
        $product = Product::create([
            'status' => 'published',
            'product_type_id' => $type->id,
            'attribute_data' => [
                'name' => new \Lunar\FieldTypes\TranslatedText([
                    'ru' => 'Тестовый товар',
                ]),
                'description' => new \Lunar\FieldTypes\TranslatedText([
                    'ru' => 'Описание товара',
                ]),
                'release_date' => new \Lunar\FieldTypes\Text(
                    now()->toDateString()
                ),
            ],
        ]);

        /**
         * 6. Variant
         */
        $taxClass = TaxClass::first() ?? TaxClass::create([
            'name' => 'Standard Tax',
            'default' => true,
        ]);

// 2. Создаем вариант
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEST-001',
            'tax_class_id' => $taxClass->id,
        ]);

        /**
         * 7. Price
         */
        Price::create([
            'price' => 15,
            'currency_id' => $currency->id,
            'priceable_type' => ProductVariant::class,
            'priceable_id' => $variant->id,
        ]);
    }
}
