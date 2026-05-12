<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Locale: string implements HasLabel
{
    case Afrikaans = 'af';
    case Albanian = 'sq';
    case Arabic = 'ar';
    case Armenian = 'hy';
    case Azerbaijani = 'az';
    case Basque = 'eu';
    case Belarusian = 'be';
    case Bengali = 'bn';
    case Bosnian = 'bs';
    case Bulgarian = 'bg';
    case Catalan = 'ca';
    case ChineseSimplified = 'zh';
    case ChineseTraditional = 'zh-TW';
    case Croatian = 'hr';
    case Czech = 'cs';
    case Danish = 'da';
    case Dutch = 'nl';
    case English = 'en';
    case Estonian = 'et';
    case Filipino = 'tl';
    case Finnish = 'fi';
    case French = 'fr';
    case Galician = 'gl';
    case Georgian = 'ka';
    case German = 'de';
    case Greek = 'el';
    case Gujarati = 'gu';
    case Hebrew = 'he';
    case Hindi = 'hi';
    case Hungarian = 'hu';
    case Icelandic = 'is';
    case Indonesian = 'id';
    case Irish = 'ga';
    case Italian = 'it';
    case Japanese = 'ja';
    case Kannada = 'kn';
    case Kazakh = 'kk';
    case Korean = 'ko';
    case Latvian = 'lv';
    case Lithuanian = 'lt';
    case Macedonian = 'mk';
    case Malay = 'ms';
    case Malayalam = 'ml';
    case Marathi = 'mr';
    case Mongolian = 'mn';
    case Nepali = 'ne';
    case Norwegian = 'nb';
    case Persian = 'fa';
    case Polish = 'pl';
    case Portuguese = 'pt';
    case PortugueseBrazilian = 'pt-BR';
    case Punjabi = 'pa';
    case Romanian = 'ro';
    case Russian = 'ru';
    case Serbian = 'sr';
    case Sinhala = 'si';
    case Slovak = 'sk';
    case Slovenian = 'sl';
    case Spanish = 'es';
    case Swahili = 'sw';
    case Swedish = 'sv';
    case Tamil = 'ta';
    case Telugu = 'te';
    case Thai = 'th';
    case Turkish = 'tr';
    case Ukrainian = 'uk';
    case Urdu = 'ur';
    case Uzbek = 'uz';
    case Vietnamese = 'vi';
    case Welsh = 'cy';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Afrikaans => 'Afrikaans',
            self::Albanian => 'Albanian',
            self::Arabic => 'Arabic',
            self::Armenian => 'Armenian',
            self::Azerbaijani => 'Azerbaijani',
            self::Basque => 'Basque',
            self::Belarusian => 'Belarusian',
            self::Bengali => 'Bengali',
            self::Bosnian => 'Bosnian',
            self::Bulgarian => 'Bulgarian',
            self::Catalan => 'Catalan',
            self::ChineseSimplified => 'Chinese (Simplified)',
            self::ChineseTraditional => 'Chinese (Traditional)',
            self::Croatian => 'Croatian',
            self::Czech => 'Czech',
            self::Danish => 'Danish',
            self::Dutch => 'Dutch',
            self::English => 'English',
            self::Estonian => 'Estonian',
            self::Filipino => 'Filipino',
            self::Finnish => 'Finnish',
            self::French => 'French',
            self::Galician => 'Galician',
            self::Georgian => 'Georgian',
            self::German => 'German',
            self::Greek => 'Greek',
            self::Gujarati => 'Gujarati',
            self::Hebrew => 'Hebrew',
            self::Hindi => 'Hindi',
            self::Hungarian => 'Hungarian',
            self::Icelandic => 'Icelandic',
            self::Indonesian => 'Indonesian',
            self::Irish => 'Irish',
            self::Italian => 'Italian',
            self::Japanese => 'Japanese',
            self::Kannada => 'Kannada',
            self::Kazakh => 'Kazakh',
            self::Korean => 'Korean',
            self::Latvian => 'Latvian',
            self::Lithuanian => 'Lithuanian',
            self::Macedonian => 'Macedonian',
            self::Malay => 'Malay',
            self::Malayalam => 'Malayalam',
            self::Marathi => 'Marathi',
            self::Mongolian => 'Mongolian',
            self::Nepali => 'Nepali',
            self::Norwegian => 'Norwegian Bokmål',
            self::Persian => 'Persian',
            self::Polish => 'Polish',
            self::Portuguese => 'Portuguese',
            self::PortugueseBrazilian => 'Portuguese (Brazil)',
            self::Punjabi => 'Punjabi',
            self::Romanian => 'Romanian',
            self::Russian => 'Russian',
            self::Serbian => 'Serbian',
            self::Sinhala => 'Sinhala',
            self::Slovak => 'Slovak',
            self::Slovenian => 'Slovenian',
            self::Spanish => 'Spanish',
            self::Swahili => 'Swahili',
            self::Swedish => 'Swedish',
            self::Tamil => 'Tamil',
            self::Telugu => 'Telugu',
            self::Thai => 'Thai',
            self::Turkish => 'Turkish',
            self::Ukrainian => 'Ukrainian',
            self::Urdu => 'Urdu',
            self::Uzbek => 'Uzbek',
            self::Vietnamese => 'Vietnamese',
            self::Welsh => 'Welsh',
        };
    }

    public function native(): string
    {
        return match ($this) {
            self::Afrikaans => 'Afrikaans',
            self::Albanian => 'shqip',
            self::Arabic => 'العربية',
            self::Armenian => 'հայերեն',
            self::Azerbaijani => 'azərbaycanca',
            self::Basque => 'euskara',
            self::Belarusian => 'беларуская',
            self::Bengali => 'বাংলা',
            self::Bosnian => 'bosanski',
            self::Bulgarian => 'български',
            self::Catalan => 'català',
            self::ChineseSimplified => '简体中文',
            self::ChineseTraditional => '繁體中文',
            self::Croatian => 'hrvatski',
            self::Czech => 'čeština',
            self::Danish => 'dansk',
            self::Dutch => 'Nederlands',
            self::English => 'English',
            self::Estonian => 'eesti',
            self::Filipino => 'Filipino',
            self::Finnish => 'suomi',
            self::French => 'français',
            self::Galician => 'galego',
            self::Georgian => 'ქართული',
            self::German => 'Deutsch',
            self::Greek => 'Ελληνικά',
            self::Gujarati => 'ગુજરાતી',
            self::Hebrew => 'עברית',
            self::Hindi => 'हिन्दी',
            self::Hungarian => 'magyar',
            self::Icelandic => 'íslenska',
            self::Indonesian => 'Bahasa Indonesia',
            self::Irish => 'Gaeilge',
            self::Italian => 'italiano',
            self::Japanese => '日本語',
            self::Kannada => 'ಕನ್ನಡ',
            self::Kazakh => 'қазақ тілі',
            self::Korean => '한국어',
            self::Latvian => 'latviešu',
            self::Lithuanian => 'lietuvių',
            self::Macedonian => 'македонски',
            self::Malay => 'Bahasa Melayu',
            self::Malayalam => 'മലയാളം',
            self::Marathi => 'मराठी',
            self::Mongolian => 'монгол',
            self::Nepali => 'नेपाली',
            self::Norwegian => 'Bokmål',
            self::Persian => 'فارسی',
            self::Polish => 'polski',
            self::Portuguese => 'português',
            self::PortugueseBrazilian => 'português do Brasil',
            self::Punjabi => 'ਪੰਜਾਬੀ',
            self::Romanian => 'română',
            self::Russian => 'русский',
            self::Serbian => 'српски',
            self::Sinhala => 'සිංහල',
            self::Slovak => 'slovenčina',
            self::Slovenian => 'slovenščina',
            self::Spanish => 'español',
            self::Swahili => 'Kiswahili',
            self::Swedish => 'svenska',
            self::Tamil => 'தமிழ்',
            self::Telugu => 'తెలుగు',
            self::Thai => 'ไทย',
            self::Turkish => 'Türkçe',
            self::Ukrainian => 'українська',
            self::Urdu => 'اردو',
            self::Uzbek => 'oʻzbekcha',
            self::Vietnamese => 'Tiếng Việt',
            self::Welsh => 'Cymraeg',
        };
    }

    public function regional(): string
    {
        return match ($this) {
            self::Afrikaans => 'af_ZA',
            self::Albanian => 'sq_AL',
            self::Arabic => 'ar_AE',
            self::Armenian => 'hy_AM',
            self::Azerbaijani => 'az_AZ',
            self::Basque => 'eu_ES',
            self::Belarusian => 'be_BY',
            self::Bengali => 'bn_BD',
            self::Bosnian => 'bs_BA',
            self::Bulgarian => 'bg_BG',
            self::Catalan => 'ca_ES',
            self::ChineseSimplified => 'zh_CN',
            self::ChineseTraditional => 'zh_TW',
            self::Croatian => 'hr_HR',
            self::Czech => 'cs_CZ',
            self::Danish => 'da_DK',
            self::Dutch => 'nl_NL',
            self::English => 'en_GB',
            self::Estonian => 'et_EE',
            self::Filipino => 'tl_PH',
            self::Finnish => 'fi_FI',
            self::French => 'fr_FR',
            self::Galician => 'gl_ES',
            self::Georgian => 'ka_GE',
            self::German => 'de_DE',
            self::Greek => 'el_GR',
            self::Gujarati => 'gu_IN',
            self::Hebrew => 'he_IL',
            self::Hindi => 'hi_IN',
            self::Hungarian => 'hu_HU',
            self::Icelandic => 'is_IS',
            self::Indonesian => 'id_ID',
            self::Irish => 'ga_IE',
            self::Italian => 'it_IT',
            self::Japanese => 'ja_JP',
            self::Kannada => 'kn_IN',
            self::Kazakh => 'kk_KZ',
            self::Korean => 'ko_KR',
            self::Latvian => 'lv_LV',
            self::Lithuanian => 'lt_LT',
            self::Macedonian => 'mk_MK',
            self::Malay => 'ms_MY',
            self::Malayalam => 'ml_IN',
            self::Marathi => 'mr_IN',
            self::Mongolian => 'mn_MN',
            self::Nepali => 'ne_NP',
            self::Norwegian => 'nb_NO',
            self::Persian => 'fa_IR',
            self::Polish => 'pl_PL',
            self::Portuguese => 'pt_PT',
            self::PortugueseBrazilian => 'pt_BR',
            self::Punjabi => 'pa_IN',
            self::Romanian => 'ro_RO',
            self::Russian => 'ru_RU',
            self::Serbian => 'sr_RS',
            self::Sinhala => 'si_LK',
            self::Slovak => 'sk_SK',
            self::Slovenian => 'sl_SI',
            self::Spanish => 'es_ES',
            self::Swahili => 'sw_KE',
            self::Swedish => 'sv_SE',
            self::Tamil => 'ta_IN',
            self::Telugu => 'te_IN',
            self::Thai => 'th_TH',
            self::Turkish => 'tr_TR',
            self::Ukrainian => 'uk_UA',
            self::Urdu => 'ur_PK',
            self::Uzbek => 'uz_UZ',
            self::Vietnamese => 'vi_VN',
            self::Welsh => 'cy_GB',
        };
    }

    public function countryCode(): string
    {
        return match ($this) {
            self::Afrikaans => 'za',
            self::Albanian => 'al',
            self::Arabic => 'ae',
            self::Armenian => 'am',
            self::Azerbaijani => 'az',
            self::Basque => 'es',
            self::Belarusian => 'by',
            self::Bengali => 'bd',
            self::Bosnian => 'ba',
            self::Bulgarian => 'bg',
            self::Catalan => 'es',
            self::ChineseSimplified => 'cn',
            self::ChineseTraditional => 'tw',
            self::Croatian => 'hr',
            self::Czech => 'cz',
            self::Danish => 'dk',
            self::Dutch => 'nl',
            self::English => 'gb',
            self::Estonian => 'ee',
            self::Filipino => 'ph',
            self::Finnish => 'fi',
            self::French => 'fr',
            self::Galician => 'es',
            self::Georgian => 'ge',
            self::German => 'de',
            self::Greek => 'gr',
            self::Gujarati => 'in',
            self::Hebrew => 'il',
            self::Hindi => 'in',
            self::Hungarian => 'hu',
            self::Icelandic => 'is',
            self::Indonesian => 'id',
            self::Irish => 'ie',
            self::Italian => 'it',
            self::Japanese => 'jp',
            self::Kannada => 'in',
            self::Kazakh => 'kz',
            self::Korean => 'kr',
            self::Latvian => 'lv',
            self::Lithuanian => 'lt',
            self::Macedonian => 'mk',
            self::Malay => 'my',
            self::Malayalam => 'in',
            self::Marathi => 'in',
            self::Mongolian => 'mn',
            self::Nepali => 'np',
            self::Norwegian => 'no',
            self::Persian => 'ir',
            self::Polish => 'pl',
            self::Portuguese => 'pt',
            self::PortugueseBrazilian => 'br',
            self::Punjabi => 'in',
            self::Romanian => 'ro',
            self::Russian => 'ru',
            self::Serbian => 'rs',
            self::Sinhala => 'lk',
            self::Slovak => 'sk',
            self::Slovenian => 'si',
            self::Spanish => 'es',
            self::Swahili => 'ke',
            self::Swedish => 'se',
            self::Tamil => 'in',
            self::Telugu => 'in',
            self::Thai => 'th',
            self::Turkish => 'tr',
            self::Ukrainian => 'ua',
            self::Urdu => 'pk',
            self::Uzbek => 'uz',
            self::Vietnamese => 'vn',
            self::Welsh => 'gb',
        };
    }

    public function flag(): string
    {
        return asset("images/langs/svg-circle/circle-country-{$this->countryCode()}.svg");
    }
}
