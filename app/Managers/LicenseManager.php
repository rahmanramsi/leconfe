<?php

namespace App\Managers;

use Illuminate\Support\Facades\App;

class LicenseManager
{
    /**
     * Get a mapping of license URL to license locale key for common
     * creative commons licenses.
     *
     * @return array
     */
    public static function getCCLicenseOptions()
    {
        return [
            'https://creativecommons.org/licenses/by-nc-nd/4.0' => __('general.submission_license_cc_by_nc_nd4'),
            'https://creativecommons.org/licenses/by-nc/4.0' => __('general.submission_license_cc_by_nc4'),
            'https://creativecommons.org/licenses/by-nc-sa/4.0' => __('general.submission_license_cc_by_nc_sa4'),
            'https://creativecommons.org/licenses/by-nd/4.0' => __('general.submission_license_cc_by_nd4'),
            'https://creativecommons.org/licenses/by/4.0' => __('general.submission_license_cc_by4'),
            'https://creativecommons.org/licenses/by-sa/4.0' => __('general.submission_license_cc_by_sa4'),
        ];
    }

    /**
     * Get the Creative Commons license badge associated with a given
     * license URL.
     *
     * @param  ?string  $ccLicenseURL  URL to creative commons license
     * @param  ?string  $locale  Optional locale to return badge in
     * @return ?string HTML code for CC license
     */
    public function getCCLicenseBadge($ccLicenseURL, $locale = null)
    {
        if (! $ccLicenseURL) {
            return null;
        }

        $licenseKeyMap = [
            '|http[s]?://(www\.)?creativecommons.org/licenses/by-nc-nd/4.0[/]?|' => 'general.submission_license_cc_by_nc_nd4_footer',
            '|http[s]?://(www\.)?creativecommons.org/licenses/by-nc/4.0[/]?|' => 'general.submission_license_cc_by_nc4_footer',
            '|http[s]?://(www\.)?creativecommons.org/licenses/by-nc-sa/4.0[/]?|' => 'general.submission_license_cc_by_nc_sa4_footer',
            '|http[s]?://(www\.)?creativecommons.org/licenses/by-nd/4.0[/]?|' => 'general.submission_license_cc_by_nd4_footer',
            '|http[s]?://(www\.)?creativecommons.org/licenses/by/4.0[/]?|' => 'general.submission_license_cc_by4_footer',
            '|http[s]?://(www\.)?creativecommons.org/licenses/by-sa/4.0[/]?|' => 'general.submission_license_cc_by_sa4_footer',
        ];

        $locale ??= App::getLocale();
        foreach ($licenseKeyMap as $pattern => $key) {
            if (preg_match($pattern, $ccLicenseURL)) {
                return __($key, [], $locale);
            }
        }

        return null;
    }
}
