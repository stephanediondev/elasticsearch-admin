<?php

namespace App\Tests\Manager;

use App\Manager\PaginatorManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaginatorManagerTest extends WebTestCase
{
    private int $total = 120;

    private int $size = 50;

    public function testPaginatePage0(): void
    {
        /**
         * @var PaginatorManager $paginatorManager
         */
        $paginatorManager = static::getContainer()->get('App\Manager\PaginatorManager');

        $paginate = [
            'total' => $this->total,
            'rows' => range(1, $this->total),
            'array_slice' => true,
            'page' => 0,
            'size' => $this->size,
        ];
        $paginate = $paginatorManager->paginate($paginate);

        $this->assertEquals($paginate['page'], 1);
        $this->assertEquals($paginate['pages'], 3);
        $this->assertEquals($paginate['rows'], range(1, $this->size));

        $this->assertEquals($paginate['start_page'], 1);
        $this->assertEquals($paginate['end_page'], 3);

        $this->assertEquals($paginate['previous'], false);
        $this->assertEquals($paginate['next'], 2);
    }

    public function testPaginatePage1(): void
    {
        /**
         * @var PaginatorManager $paginatorManager
         */
        $paginatorManager = static::getContainer()->get('App\Manager\PaginatorManager');

        $paginate = [
            'total' => $this->total,
            'rows' => range(1, $this->total),
            'array_slice' => true,
            'page' => 1,
            'size' => $this->size,
        ];
        $paginate = $paginatorManager->paginate($paginate);

        $this->assertEquals($paginate['page'], 1);
        $this->assertEquals($paginate['pages'], 3);
        $this->assertEquals($paginate['rows'], range(1, $this->size));

        $this->assertEquals($paginate['start_page'], 1);
        $this->assertEquals($paginate['end_page'], 3);

        $this->assertEquals($paginate['previous'], false);
        $this->assertEquals($paginate['next'], 2);
    }

    public function testPaginatePage2(): void
    {
        /**
         * @var PaginatorManager $paginatorManager
         */
        $paginatorManager = static::getContainer()->get('App\Manager\PaginatorManager');

        $paginate = [
            'total' => $this->total,
            'rows' => range(1, $this->total),
            'array_slice' => true,
            'page' => 2,
            'size' => $this->size,
        ];
        $paginate = $paginatorManager->paginate($paginate);

        $this->assertEquals($paginate['page'], 2);
        $this->assertEquals($paginate['pages'], 3);
        $this->assertEquals($paginate['rows'], range($this->size + 1, $this->size * 2));

        $this->assertEquals($paginate['start_page'], 1);
        $this->assertEquals($paginate['end_page'], 3);

        $this->assertEquals($paginate['previous'], 1);
        $this->assertEquals($paginate['next'], 3);
    }

    public function testPaginatePage3(): void
    {
        /**
         * @var PaginatorManager $paginatorManager
         */
        $paginatorManager = static::getContainer()->get('App\Manager\PaginatorManager');

        $paginate = [
            'total' => $this->total,
            'rows' => range(1, $this->total),
            'array_slice' => true,
            'page' => 3,
            'size' => $this->size,
        ];
        $paginate = $paginatorManager->paginate($paginate);

        $this->assertEquals($paginate['page'], 3);
        $this->assertEquals($paginate['pages'], 3);
        $this->assertEquals($paginate['rows'], range(($this->size * 2) + 1, $this->total));

        $this->assertEquals($paginate['start_page'], 1);
        $this->assertEquals($paginate['end_page'], 3);

        $this->assertEquals($paginate['previous'], 2);
        $this->assertEquals($paginate['next'], false);
    }

    public function testPaginatePage4(): void
    {
        /**
         * @var PaginatorManager $paginatorManager
         */
        $paginatorManager = static::getContainer()->get('App\Manager\PaginatorManager');

        $paginate = [
            'total' => $this->total,
            'rows' => range(1, $this->total),
            'array_slice' => true,
            'page' => 4,
            'size' => $this->size,
        ];
        $paginate = $paginatorManager->paginate($paginate);

        $this->assertEquals($paginate['page'], 1);
        $this->assertEquals($paginate['pages'], 3);
        $this->assertEquals($paginate['rows'], range(1, $this->size));

        $this->assertEquals($paginate['start_page'], 1);
        $this->assertEquals($paginate['end_page'], 3);

        $this->assertEquals($paginate['previous'], false);
        $this->assertEquals($paginate['next'], 2);
    }
}
