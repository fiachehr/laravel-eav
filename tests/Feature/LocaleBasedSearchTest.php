<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Query\EavQueryBuilder;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class LocaleBasedSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    /** @test */
    public function it_can_search_by_text_value_in_specific_locale(): void
    {
        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('title', 'Laptop', 'en');
        $product1->setEavAttributeValue('title', 'لپ تاپ', 'fa');
        $product1->setEavAttributeValue('title', 'Laptop', 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Desktop', 'en');
        $product2->setEavAttributeValue('title', 'دسکتاپ', 'fa');
        $product2->setEavAttributeValue('title', 'Desktop', 'de');

        // Search in English locale
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereText('title', 'Laptop')
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());

        // Search in Persian locale
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereText('title', 'لپ تاپ')
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_search_by_text_like_in_different_locales(): void
    {
        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('title', 'Laptop Computer', 'en');
        $product1->setEavAttributeValue('title', 'رایانه لپ تاپ', 'fa');
        $product1->setEavAttributeValue('title', 'Laptop Computer', 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Desktop Computer', 'en');
        $product2->setEavAttributeValue('title', 'رایانه دسکتاپ', 'fa');
        $product2->setEavAttributeValue('title', 'Desktop Computer', 'de');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('title', 'Mobile Phone', 'en');
        $product3->setEavAttributeValue('title', 'تلفن همراه', 'fa');
        $product3->setEavAttributeValue('title', 'Mobiltelefon', 'de');

        // Search in English
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereTextLike('title', 'Computer')
            ->getAttributableIds();

        $this->assertCount(2, $ids);
        $this->assertTrue($ids->contains(1));
        $this->assertTrue($ids->contains(2));

        // Search in Persian
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereTextLike('title', 'رایانه')
            ->getAttributableIds();

        $this->assertCount(2, $ids);
        $this->assertTrue($ids->contains(1));
        $this->assertTrue($ids->contains(2));
    }

    /** @test */
    public function it_can_filter_by_number_range_with_locale(): void
    {
        $priceAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Price',
            'slug' => 'price',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('price', 100, 'en');
        $product1->setEavAttributeValue('price', 100, 'fa');
        $product1->setEavAttributeValue('price', 100, 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('price', 200, 'en');
        $product2->setEavAttributeValue('price', 200, 'fa');
        $product2->setEavAttributeValue('price', 200, 'de');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('price', 300, 'en');
        $product3->setEavAttributeValue('price', 300, 'fa');
        $product3->setEavAttributeValue('price', 300, 'de');

        // Filter by number range (numbers are locale-independent)
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereNumberBetween('price', 150, 250)
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(2, $ids->first());
    }

    /** @test */
    public function it_can_search_by_multiple_attributes_with_different_locales(): void
    {
        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $categoryAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Category',
            'slug' => 'category',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('title', 'Laptop', 'en');
        $product1->setEavAttributeValue('title', 'لپ تاپ', 'fa');
        $product1->setEavAttributeValue('category', 'Electronics', 'en');
        $product1->setEavAttributeValue('category', 'الکترونیک', 'fa');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Laptop', 'en');
        $product2->setEavAttributeValue('title', 'لپ تاپ', 'fa');
        $product2->setEavAttributeValue('category', 'Accessories', 'en');
        $product2->setEavAttributeValue('category', 'لوازم جانبی', 'fa');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('title', 'Desktop', 'en');
        $product3->setEavAttributeValue('title', 'دسکتاپ', 'fa');
        $product3->setEavAttributeValue('category', 'Electronics', 'en');
        $product3->setEavAttributeValue('category', 'الکترونیک', 'fa');

        // Search by multiple attributes (AND) — use whereMultiple; chained whereText targets one attribute_id per row
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereMultiple([
                ['attribute' => 'title', 'value' => 'Laptop'],
                ['attribute' => 'category', 'value' => 'Electronics'],
            ])
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereMultiple([
                ['attribute' => 'title', 'value' => 'لپ تاپ'],
                ['attribute' => 'category', 'value' => 'الکترونیک'],
            ])
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_search_across_all_locales(): void
    {
        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('title', 'Laptop', 'en');
        $product1->setEavAttributeValue('title', 'لپ تاپ', 'fa');
        $product1->setEavAttributeValue('title', 'Laptop', 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Desktop', 'en');
        $product2->setEavAttributeValue('title', 'دسکتاپ', 'fa');
        $product2->setEavAttributeValue('title', 'Desktop', 'de');

        // Search should find products regardless of locale when searching by value
        // This tests that the search works across all locales
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereTextLike('title', 'Laptop')
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_filter_by_date_range_with_locale(): void
    {
        $dateAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Created Date',
            'slug' => 'created-date',
            'type' => AttributeType::DATE->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('created-date', '2024-01-15', 'en');
        $product1->setEavAttributeValue('created-date', '2024-01-15', 'fa');
        $product1->setEavAttributeValue('created-date', '2024-01-15', 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('created-date', '2024-06-20', 'en');
        $product2->setEavAttributeValue('created-date', '2024-06-20', 'fa');
        $product2->setEavAttributeValue('created-date', '2024-06-20', 'de');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('created-date', '2024-12-25', 'en');
        $product3->setEavAttributeValue('created-date', '2024-12-25', 'fa');
        $product3->setEavAttributeValue('created-date', '2024-12-25', 'de');

        // Filter by date range (dates are locale-independent)
        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereDateBetween('created-date', '2024-01-01', '2024-06-30')
            ->getAttributableIds();

        $this->assertCount(2, $ids);
        $this->assertTrue($ids->contains(1));
        $this->assertTrue($ids->contains(2));
    }
}

