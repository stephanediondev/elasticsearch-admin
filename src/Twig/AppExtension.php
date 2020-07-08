<?php

namespace App\Twig;

use App\Model\ElasticsearchIndexModel;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('human_filesize', [$this, 'humanFilesize']),
            new TwigFilter('human_version', [$this, 'humanVersion']),
            new TwigFilter('human_datetime', [$this, 'humanDatetime']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('check_version', [$this, 'checkVersion']),
            new TwigFunction('has_feature', [$this, 'hasFeature']),
            new TwigFunction('has_plugin', [$this, 'hasPlugin']),
            new TwigFunction('retrieve_sort', [$this, 'retrieveSort']),
            new TwigFunction('retrieve_value', [$this, 'retrieveValue']),
        ];
    }

    public function humanFilesize($size, $precision = 2)
    {
        static $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        return round($size, $precision).' '.$units[$i];
    }

    public function humanVersion($version)
    {
        /*
        * The logic for ID is: XXYYZZAA, where XX is major version, YY is minor version, ZZ is revision, and AA is alpha/beta/rc indicator AA
        * values below 25 are for alpha builder (since 5.0), and above 25 and below 50 are beta builds, and below 99 are RC builds, with 99
        * indicating a release the (internal) format of the id is there so we can easily do after/before checks on the id
        *
        * IMPORTANT: Unreleased vs. Released Versions
        *
        * All listed versions MUST be released versions, except the last major, the last minor and the last revison. ONLY those are required
        * as unreleased versions.
        *
        * Example: assume the last release is 7.3.0
        * The unreleased last major is the next major release, e.g. _8_.0.0
        * The unreleased last minor is the current major with a upped minor: 7._4_.0
        * The unreleased revision is the very release with a upped revision 7.3._1_
        */

        $aa = substr($version, -2);
        $zz = substr($version, -4, -2);
        $yy = substr($version, -6, -4);
        $xx = substr($version, 0, -6);

        return $xx.'.'.intval($yy).'.'.intval($zz);
    }

    public function humanDatetime($datetime)
    {
        return date('D, d M Y H:i', substr($datetime, 0, -3));
    }

    public function checkVersion(array $root, string $versionGoal): bool
    {
        if (true == isset($root['version']) && true == isset($root['version']['number']) && 0 <= version_compare($root['version']['number'], $versionGoal)) {
            return true;
        }

        return false;
    }

    public function hasFeature(array $xpack, string $feature): bool
    {
        if (true == isset($xpack['features'][$feature]) && true == $xpack['features'][$feature]['available'] && true == $xpack['features'][$feature]['enabled']) {
            return true;
        }

        return false;
    }

    public function hasPlugin(array $plugins, string $plugin): bool
    {
        if (true == in_array($plugin, $plugins)) {
            return true;
        }

        return false;
    }

    public function retrieveSort(ElasticsearchIndexModel $index, string $field)
    {
        $sort = false;

        $mappingsFlat = $index->getMappingsFlat();

        if (true == in_array($mappingsFlat[$field]['type'], ['keyword', 'date', 'long', 'integer'])) {
            $sort = $field;

        } elseif (true == isset($mappingsFlat[$field]['fields'])) {
            $foundKeyword = false;
            foreach ($mappingsFlat[$field]['fields'] as $fieldSub => $propertiesSub) {
                if (false == $foundKeyword && true == isset($propertiesSub['type']) && 'keyword' == $propertiesSub['type']) {
                    $foundKeyword = true;
                    $sort = $field.'.'.$fieldSub;
                }
            }
        }

        return $sort;
    }

    public function retrieveValue(array $source, string $field)
    {
        $value = false;

        if (true == isset($source[$field])) {
            return $source[$field];
        } else {
            $keys = explode('.', $field);

            $arr = $source;
            foreach ($keys as $key) {
                $arr = &$arr[$key];
            }

            $value = $arr;
        }

        return $value;
    }
}
