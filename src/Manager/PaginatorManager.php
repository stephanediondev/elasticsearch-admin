<?php

namespace App\Manager;

class PaginatorManager
{
    public function paginate($paginate): array
    {
        $slice = 2;

        if (0 == $paginate['size']) {
            $paginate['pages'] = 0;
        } else {
            $paginate['pages'] = ceil($paginate['total'] / $paginate['size']);
        }

        $pagesSlice = [];
        $paginate['start_page'] = $paginate['page'] - $slice;
        if (0 >= $paginate['start_page']) {
            $paginate['start_page'] = 1;
        }
        $paginate['end_page'] = $paginate['page'] + $slice;
        if ($paginate['pages'] < $paginate['end_page']) {
            $paginate['end_page'] = $paginate['pages'];
        }
        for ($i=$paginate['start_page']; $i<=$paginate['end_page']; $i++) {
            $pagesSlice[] = $i;
        }
        $paginate['pages_slice'] = $pagesSlice;

        if (false === in_array(1, $paginate['pages_slice'])) {
            $paginate['first'] = 1;
        } else {
            $paginate['first'] = false;
        }

        if (false === in_array($paginate['pages'], $paginate['pages_slice'])) {
            $paginate['last'] = $paginate['pages'];
        } else {
            $paginate['last'] = false;
        }

        if (1 < $paginate['page']) {
            $paginate['previous'] = $paginate['page'] - 1;
        } else {
            $paginate['previous'] = false;
        }

        if ($paginate['pages'] > $paginate['page']) {
            $paginate['next'] = $paginate['page'] + 1;
        } else {
            $paginate['next'] = false;
        }

        return $paginate;
    }
}
