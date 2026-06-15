<?php

namespace Config;

/**
 * Static Quran metadata for Hifz program seeding (Indo-Pak 16-line mushaf).
 */
class QuranReference
{
    public const LAYOUT_CODE = 'indopak_16';
    public const LINES_PER_PAGE = 16;
    public const TOTAL_PAGES = 611;

    /** @var list<int> Ayah count per surah (index 0 = surah 1) */
    public static array $surahAyahCounts = [
        7, 286, 200, 176, 120, 165, 206, 75, 129, 109, 123, 111, 43, 52, 99, 128, 111, 110, 98, 135,
        112, 78, 118, 64, 77, 227, 93, 88, 69, 60, 34, 30, 73, 54, 45, 83, 182, 88, 75, 85, 54, 53, 89, 59,
        37, 35, 38, 29, 18, 45, 60, 49, 62, 55, 78, 96, 29, 22, 24, 13, 14, 11, 11, 18, 12, 12, 30, 52, 52,
        44, 28, 28, 20, 56, 40, 31, 50, 40, 46, 42, 29, 19, 36, 25, 22, 17, 19, 26, 30, 20, 15, 21, 11, 8,
        8, 19, 5, 8, 8, 11, 11, 8, 3, 9, 5, 4, 7, 3, 6, 3, 5, 4, 5, 6,
    ];

    /** @var list<array{name_en:string,name_ar:string,revelation:int}> */
    public static array $surahMeta = [
        ['name_en' => 'Al-Fatiha', 'name_ar' => 'الفاتحة', 'revelation' => 5],
        ['name_en' => 'Al-Baqarah', 'name_ar' => 'البقرة', 'revelation' => 87],
        ['name_en' => 'Al-Imran', 'name_ar' => 'آل عمران', 'revelation' => 89],
        ['name_en' => 'An-Nisa', 'name_ar' => 'النساء', 'revelation' => 92],
        ['name_en' => 'Al-Maidah', 'name_ar' => 'المائدة', 'revelation' => 112],
        ['name_en' => 'Al-Anam', 'name_ar' => 'الأنعام', 'revelation' => 55],
        ['name_en' => 'Al-Araf', 'name_ar' => 'الأعراف', 'revelation' => 39],
        ['name_en' => 'Al-Anfal', 'name_ar' => 'الأنفال', 'revelation' => 88],
        ['name_en' => 'At-Tawbah', 'name_ar' => 'التوبة', 'revelation' => 113],
        ['name_en' => 'Yunus', 'name_ar' => 'يونس', 'revelation' => 51],
        ['name_en' => 'Hud', 'name_ar' => 'هود', 'revelation' => 52],
        ['name_en' => 'Yusuf', 'name_ar' => 'يوسف', 'revelation' => 53],
        ['name_en' => 'Ar-Raad', 'name_ar' => 'الرعد', 'revelation' => 96],
        ['name_en' => 'Ibrahim', 'name_ar' => 'إبراهيم', 'revelation' => 72],
        ['name_en' => 'Al-Hijr', 'name_ar' => 'الحجر', 'revelation' => 54],
        ['name_en' => 'An-Nahl', 'name_ar' => 'النحل', 'revelation' => 70],
        ['name_en' => 'Al-Isra', 'name_ar' => 'الإسراء', 'revelation' => 50],
        ['name_en' => 'Al-Kahf', 'name_ar' => 'الكهف', 'revelation' => 69],
        ['name_en' => 'Maryam', 'name_ar' => 'مريم', 'revelation' => 44],
        ['name_en' => 'Ta-Ha', 'name_ar' => 'طه', 'revelation' => 45],
        ['name_en' => 'Al-Anbiya', 'name_ar' => 'الأنبياء', 'revelation' => 73],
        ['name_en' => 'Al-Hajj', 'name_ar' => 'الحج', 'revelation' => 103],
        ['name_en' => 'Al-Muminun', 'name_ar' => 'المؤمنون', 'revelation' => 74],
        ['name_en' => 'An-Nur', 'name_ar' => 'النور', 'revelation' => 102],
        ['name_en' => 'Al-Furqan', 'name_ar' => 'الفرقان', 'revelation' => 42],
        ['name_en' => 'Ash-Shuara', 'name_ar' => 'الشعراء', 'revelation' => 47],
        ['name_en' => 'An-Naml', 'name_ar' => 'النمل', 'revelation' => 48],
        ['name_en' => 'Al-Qasas', 'name_ar' => 'القصص', 'revelation' => 49],
        ['name_en' => 'Al-Ankabut', 'name_ar' => 'العنكبوت', 'revelation' => 85],
        ['name_en' => 'Ar-Rum', 'name_ar' => 'الروم', 'revelation' => 84],
        ['name_en' => 'Luqman', 'name_ar' => 'لقمان', 'revelation' => 57],
        ['name_en' => 'As-Sajdah', 'name_ar' => 'السجدة', 'revelation' => 75],
        ['name_en' => 'Al-Ahzab', 'name_ar' => 'الأحزاب', 'revelation' => 90],
        ['name_en' => 'Saba', 'name_ar' => 'سبأ', 'revelation' => 58],
        ['name_en' => 'Fatir', 'name_ar' => 'فاطر', 'revelation' => 43],
        ['name_en' => 'Ya-Sin', 'name_ar' => 'يس', 'revelation' => 41],
        ['name_en' => 'As-Saffat', 'name_ar' => 'الصافات', 'revelation' => 56],
        ['name_en' => 'Sad', 'name_ar' => 'ص', 'revelation' => 38],
        ['name_en' => 'Az-Zumar', 'name_ar' => 'الزمر', 'revelation' => 59],
        ['name_en' => 'Ghafir', 'name_ar' => 'غافر', 'revelation' => 60],
        ['name_en' => 'Fussilat', 'name_ar' => 'فصلت', 'revelation' => 61],
        ['name_en' => 'Ash-Shura', 'name_ar' => 'الشورى', 'revelation' => 62],
        ['name_en' => 'Az-Zukhruf', 'name_ar' => 'الزخرف', 'revelation' => 63],
        ['name_en' => 'Ad-Dukhan', 'name_ar' => 'الدخان', 'revelation' => 64],
        ['name_en' => 'Al-Jathiyah', 'name_ar' => 'الجاثية', 'revelation' => 65],
        ['name_en' => 'Al-Ahqaf', 'name_ar' => 'الأحقاف', 'revelation' => 66],
        ['name_en' => 'Muhammad', 'name_ar' => 'محمد', 'revelation' => 95],
        ['name_en' => 'Al-Fath', 'name_ar' => 'الفتح', 'revelation' => 111],
        ['name_en' => 'Al-Hujurat', 'name_ar' => 'الحجرات', 'revelation' => 106],
        ['name_en' => 'Qaf', 'name_ar' => 'ق', 'revelation' => 34],
        ['name_en' => 'Adh-Dhariyat', 'name_ar' => 'الذاريات', 'revelation' => 67],
        ['name_en' => 'At-Tur', 'name_ar' => 'الطور', 'revelation' => 76],
        ['name_en' => 'An-Najm', 'name_ar' => 'النجم', 'revelation' => 23],
        ['name_en' => 'Al-Qamar', 'name_ar' => 'القمر', 'revelation' => 37],
        ['name_en' => 'Ar-Rahman', 'name_ar' => 'الرحمن', 'revelation' => 97],
        ['name_en' => 'Al-Waqiah', 'name_ar' => 'الواقعة', 'revelation' => 46],
        ['name_en' => 'Al-Hadid', 'name_ar' => 'الحديد', 'revelation' => 94],
        ['name_en' => 'Al-Mujadila', 'name_ar' => 'المجادلة', 'revelation' => 105],
        ['name_en' => 'Al-Hashr', 'name_ar' => 'الحشر', 'revelation' => 101],
        ['name_en' => 'Al-Mumtahanah', 'name_ar' => 'الممتحنة', 'revelation' => 91],
        ['name_en' => 'As-Saff', 'name_ar' => 'الصف', 'revelation' => 108],
        ['name_en' => 'Al-Jumuah', 'name_ar' => 'الجمعة', 'revelation' => 109],
        ['name_en' => 'Al-Munafiqun', 'name_ar' => 'المنافقون', 'revelation' => 104],
        ['name_en' => 'At-Taghabun', 'name_ar' => 'التغابن', 'revelation' => 107],
        ['name_en' => 'At-Talaq', 'name_ar' => 'الطلاق', 'revelation' => 99],
        ['name_en' => 'At-Tahrim', 'name_ar' => 'التحريم', 'revelation' => 110],
        ['name_en' => 'Al-Mulk', 'name_ar' => 'الملك', 'revelation' => 77],
        ['name_en' => 'Al-Qalam', 'name_ar' => 'القلم', 'revelation' => 2],
        ['name_en' => 'Al-Haqqah', 'name_ar' => 'الحاقة', 'revelation' => 78],
        ['name_en' => 'Al-Maarij', 'name_ar' => 'المعارج', 'revelation' => 79],
        ['name_en' => 'Nuh', 'name_ar' => 'نوح', 'revelation' => 71],
        ['name_en' => 'Al-Jinn', 'name_ar' => 'الجن', 'revelation' => 40],
        ['name_en' => 'Al-Muzzammil', 'name_ar' => 'المزمل', 'revelation' => 3],
        ['name_en' => 'Al-Muddaththir', 'name_ar' => 'المدثر', 'revelation' => 4],
        ['name_en' => 'Al-Qiyamah', 'name_ar' => 'القيامة', 'revelation' => 31],
        ['name_en' => 'Al-Insan', 'name_ar' => 'الإنسان', 'revelation' => 98],
        ['name_en' => 'Al-Mursalat', 'name_ar' => 'المرسلات', 'revelation' => 33],
        ['name_en' => 'An-Naba', 'name_ar' => 'النبأ', 'revelation' => 80],
        ['name_en' => 'An-Naziat', 'name_ar' => 'النازعات', 'revelation' => 81],
        ['name_en' => 'Abasa', 'name_ar' => 'عبس', 'revelation' => 24],
        ['name_en' => 'At-Takwir', 'name_ar' => 'التكوير', 'revelation' => 25],
        ['name_en' => 'Al-Infitar', 'name_ar' => 'الانفطار', 'revelation' => 82],
        ['name_en' => 'Al-Mutaffifin', 'name_ar' => 'المطففين', 'revelation' => 86],
        ['name_en' => 'Al-Inshiqaq', 'name_ar' => 'الانشقاق', 'revelation' => 83],
        ['name_en' => 'Al-Buruj', 'name_ar' => 'البروج', 'revelation' => 85],
        ['name_en' => 'At-Tariq', 'name_ar' => 'الطارق', 'revelation' => 86],
        ['name_en' => 'Al-Ala', 'name_ar' => 'الأعلى', 'revelation' => 8],
        ['name_en' => 'Al-Ghashiyah', 'name_ar' => 'الغاشية', 'revelation' => 68],
        ['name_en' => 'Al-Fajr', 'name_ar' => 'الفجر', 'revelation' => 10],
        ['name_en' => 'Al-Balad', 'name_ar' => 'البلد', 'revelation' => 35],
        ['name_en' => 'Ash-Shams', 'name_ar' => 'الشمس', 'revelation' => 26],
        ['name_en' => 'Al-Layl', 'name_ar' => 'الليل', 'revelation' => 9],
        ['name_en' => 'Ad-Duha', 'name_ar' => 'الضحى', 'revelation' => 11],
        ['name_en' => 'Ash-Sharh', 'name_ar' => 'الشرح', 'revelation' => 12],
        ['name_en' => 'At-Tin', 'name_ar' => 'التين', 'revelation' => 6],
        ['name_en' => 'Al-Alaq', 'name_ar' => 'العلق', 'revelation' => 19],
        ['name_en' => 'Al-Qadr', 'name_ar' => 'القدر', 'revelation' => 25],
        ['name_en' => 'Al-Bayyinah', 'name_ar' => 'البينة', 'revelation' => 100],
        ['name_en' => 'Az-Zalzalah', 'name_ar' => 'الزلزلة', 'revelation' => 93],
        ['name_en' => 'Al-Adiyat', 'name_ar' => 'العاديات', 'revelation' => 14],
        ['name_en' => 'Al-Qariah', 'name_ar' => 'القارعة', 'revelation' => 30],
        ['name_en' => 'At-Takathur', 'name_ar' => 'التكاثر', 'revelation' => 16],
        ['name_en' => 'Al-Asr', 'name_ar' => 'العصر', 'revelation' => 13],
        ['name_en' => 'Al-Humazah', 'name_ar' => 'الهمزة', 'revelation' => 32],
        ['name_en' => 'Al-Fil', 'name_ar' => 'الفيل', 'revelation' => 15],
        ['name_en' => 'Quraysh', 'name_ar' => 'قريش', 'revelation' => 29],
        ['name_en' => 'Al-Maun', 'name_ar' => 'الماعون', 'revelation' => 17],
        ['name_en' => 'Al-Kawthar', 'name_ar' => 'الكوثر', 'revelation' => 18],
        ['name_en' => 'Al-Kafirun', 'name_ar' => 'الكافرون', 'revelation' => 36],
        ['name_en' => 'An-Nasr', 'name_ar' => 'النصر', 'revelation' => 110],
        ['name_en' => 'Al-Masad', 'name_ar' => 'المسد', 'revelation' => 111],
        ['name_en' => 'Al-Ikhlas', 'name_ar' => 'الإخلاص', 'revelation' => 22],
        ['name_en' => 'Al-Falaq', 'name_ar' => 'الفلق', 'revelation' => 20],
        ['name_en' => 'An-Nas', 'name_ar' => 'الناس', 'revelation' => 21],
    ];

    /**
     * Standard juz boundaries (surah + ayah).
     *
     * @var list<array{start:array{surah:int,ayah:int},end:array{surah:int,ayah:int}}>
     */
    public static array $juzBoundaries = [
        ['start' => ['surah' => 1, 'ayah' => 1], 'end' => ['surah' => 2, 'ayah' => 141]],
        ['start' => ['surah' => 2, 'ayah' => 142], 'end' => ['surah' => 2, 'ayah' => 252]],
        ['start' => ['surah' => 2, 'ayah' => 253], 'end' => ['surah' => 3, 'ayah' => 92]],
        ['start' => ['surah' => 3, 'ayah' => 93], 'end' => ['surah' => 4, 'ayah' => 23]],
        ['start' => ['surah' => 4, 'ayah' => 24], 'end' => ['surah' => 4, 'ayah' => 147]],
        ['start' => ['surah' => 4, 'ayah' => 148], 'end' => ['surah' => 5, 'ayah' => 81]],
        ['start' => ['surah' => 5, 'ayah' => 82], 'end' => ['surah' => 6, 'ayah' => 110]],
        ['start' => ['surah' => 6, 'ayah' => 111], 'end' => ['surah' => 7, 'ayah' => 87]],
        ['start' => ['surah' => 7, 'ayah' => 88], 'end' => ['surah' => 8, 'ayah' => 40]],
        ['start' => ['surah' => 8, 'ayah' => 41], 'end' => ['surah' => 9, 'ayah' => 92]],
        ['start' => ['surah' => 9, 'ayah' => 93], 'end' => ['surah' => 11, 'ayah' => 5]],
        ['start' => ['surah' => 11, 'ayah' => 6], 'end' => ['surah' => 12, 'ayah' => 52]],
        ['start' => ['surah' => 12, 'ayah' => 53], 'end' => ['surah' => 14, 'ayah' => 52]],
        ['start' => ['surah' => 15, 'ayah' => 1], 'end' => ['surah' => 16, 'ayah' => 128]],
        ['start' => ['surah' => 17, 'ayah' => 1], 'end' => ['surah' => 18, 'ayah' => 74]],
        ['start' => ['surah' => 18, 'ayah' => 75], 'end' => ['surah' => 20, 'ayah' => 135]],
        ['start' => ['surah' => 21, 'ayah' => 1], 'end' => ['surah' => 22, 'ayah' => 78]],
        ['start' => ['surah' => 22, 'ayah' => 79], 'end' => ['surah' => 25, 'ayah' => 20]],
        ['start' => ['surah' => 25, 'ayah' => 21], 'end' => ['surah' => 27, 'ayah' => 55]],
        ['start' => ['surah' => 27, 'ayah' => 56], 'end' => ['surah' => 29, 'ayah' => 45]],
        ['start' => ['surah' => 29, 'ayah' => 46], 'end' => ['surah' => 33, 'ayah' => 30]],
        ['start' => ['surah' => 33, 'ayah' => 31], 'end' => ['surah' => 36, 'ayah' => 27]],
        ['start' => ['surah' => 36, 'ayah' => 28], 'end' => ['surah' => 39, 'ayah' => 31]],
        ['start' => ['surah' => 39, 'ayah' => 32], 'end' => ['surah' => 41, 'ayah' => 46]],
        ['start' => ['surah' => 41, 'ayah' => 47], 'end' => ['surah' => 45, 'ayah' => 37]],
        ['start' => ['surah' => 46, 'ayah' => 1], 'end' => ['surah' => 51, 'ayah' => 30]],
        ['start' => ['surah' => 51, 'ayah' => 31], 'end' => ['surah' => 57, 'ayah' => 29]],
        ['start' => ['surah' => 57, 'ayah' => 30], 'end' => ['surah' => 66, 'ayah' => 12]],
        ['start' => ['surah' => 66, 'ayah' => 13], 'end' => ['surah' => 77, 'ayah' => 50]],
        ['start' => ['surah' => 78, 'ayah' => 1], 'end' => ['surah' => 114, 'ayah' => 6]],
    ];

    /** Indo-Pak 16-line mushaf: starting page for each juz (1–30). */
    public static array $juzStartPages = [
        1, 21, 39, 57, 75, 93, 111, 129, 147, 165,
        183, 201, 219, 237, 255, 273, 291, 309, 327, 345,
        363, 381, 399, 417, 435, 453, 471, 489, 507, 525,
    ];

    /**
     * Traditional Indo-Pak para (juz) names — distinct from surah names.
     *
     * @var list<array{name_ar:string,name_en:string}>
     */
    public static array $juzMeta = [
        ['name_ar' => 'الم', 'name_en' => 'Alif Laam Meem'],
        ['name_ar' => 'سيقول', 'name_en' => 'Sayaqool'],
        ['name_ar' => 'تلك الرسل', 'name_en' => 'Tilkar Rusul'],
        ['name_ar' => 'لن تنالوا', 'name_en' => 'Lan Tanaloo'],
        ['name_ar' => 'والمحصنات', 'name_en' => 'Wal Muhsanat'],
        ['name_ar' => 'لا يحب الله', 'name_en' => 'La Yuhibbullah'],
        ['name_ar' => 'وإذا سمعوا', 'name_en' => 'Wa Iza Samiu'],
        ['name_ar' => 'ولو أننا', 'name_en' => 'Wa Law Annana'],
        ['name_ar' => 'قال الملأ', 'name_en' => 'Qalal Malao'],
        ['name_ar' => 'واعلموا', 'name_en' => 'Wa\'lamu'],
        ['name_ar' => 'يعتذرون', 'name_en' => 'Ya\'tazirun'],
        ['name_ar' => 'وما من دابة', 'name_en' => 'Wa Ma Min Dabbah'],
        ['name_ar' => 'وما أبرئ', 'name_en' => 'Wa Ma Ubarri\'u'],
        ['name_ar' => 'ربما', 'name_en' => 'Rubama'],
        ['name_ar' => 'سبحان الذي', 'name_en' => 'Subhanalladhi'],
        ['name_ar' => 'قال ألم', 'name_en' => 'Qal Alam'],
        ['name_ar' => 'اقترب', 'name_en' => 'Iqtaraba'],
        ['name_ar' => 'قد أفلح', 'name_en' => 'Qad Aflaha'],
        ['name_ar' => 'وقال الذين', 'name_en' => 'Wa Qalallazina'],
        ['name_ar' => 'أمن خلق', 'name_en' => 'Amman Khalaq'],
        ['name_ar' => 'اتل ما أوحي', 'name_en' => 'Utlu Ma Oohiya'],
        ['name_ar' => 'ومن يقنت', 'name_en' => 'Wa Man Yaqnut'],
        ['name_ar' => 'وما لي', 'name_en' => 'Wa Ma Li'],
        ['name_ar' => 'فمن أظلم', 'name_en' => 'Faman Azlamu'],
        ['name_ar' => 'إليه يرد', 'name_en' => 'Ilayhi Yuradd'],
        ['name_ar' => 'حم', 'name_en' => 'Ha Meem'],
        ['name_ar' => 'قال فما خطبكم', 'name_en' => 'Qala Fama Khatbukum'],
        ['name_ar' => 'قد سمع الله', 'name_en' => 'Qad Sami\'allah'],
        ['name_ar' => 'تبارك الذي', 'name_en' => 'Tabarakalladhi'],
        ['name_ar' => 'عم', 'name_en' => 'Amma'],
    ];

    /**
     * Compare two surah:ayah positions in Quran order (-1, 0, 1).
     */
    public static function compareAyah(int $surahA, int $ayahA, int $surahB, int $ayahB): int
    {
        if ($surahA !== $surahB) {
            return $surahA <=> $surahB;
        }

        return $ayahA <=> $ayahB;
    }

    /**
     * Juz number containing the given surah:ayah (1–30).
     */
    public static function juzForAyah(int $surah, int $ayah): int
    {
        if ($surah < 1 || $surah > 114 || $ayah < 1) {
            return 1;
        }

        for ($j = 1; $j <= 30; $j++) {
            $b = self::$juzBoundaries[$j - 1];
            if (
                self::compareAyah($surah, $ayah, $b['start']['surah'], $b['start']['ayah']) >= 0
                && self::compareAyah($surah, $ayah, $b['end']['surah'], $b['end']['ayah']) <= 0
            ) {
                return $j;
            }
        }

        return 1;
    }

    /**
     * Portions of one surah that fall in each juz (reading order).
     *
     * @return list<array{juz:int,ayah_from:int,ayah_to:int}>
     */
    public static function surahJuzSegments(int $surahId): array
    {
        if ($surahId < 1 || $surahId > 114) {
            return [];
        }

        $max = (int) (self::$surahAyahCounts[$surahId - 1] ?? 0);
        if ($max <= 0) {
            return [];
        }

        $segments = [];

        for ($j = 1; $j <= 30; $j++) {
            $b = self::$juzBoundaries[$j - 1];
            $from   = null;
            $to     = null;
            $startS = (int) $b['start']['surah'];
            $startA = (int) $b['start']['ayah'];
            $endS   = (int) $b['end']['surah'];
            $endA   = (int) $b['end']['ayah'];

            if ($surahId > $startS && $surahId < $endS) {
                $from = 1;
                $to   = $max;
            } elseif ($surahId === $startS && $surahId === $endS) {
                $from = $startA;
                $to   = min($max, $endA);
            } elseif ($surahId === $startS) {
                $from = $startA;
                $to   = $max;
            } elseif ($surahId === $endS) {
                $from = 1;
                $to   = min($max, $endA);
            }

            if ($from !== null && $to !== null && $from <= $to) {
                $segments[] = ['juz' => $j, 'ayah_from' => $from, 'ayah_to' => $to];
            }
        }

        return $segments;
    }

    /**
     * Expand juz boundaries into ordered ayah tokens.
     *
     * @return list<array{surah:int,ayah:int,juz:int}>
     */
    public static function ayahsInJuz(int $juzNo): array
    {
        $idx = $juzNo - 1;
        if ($idx < 0 || $idx >= count(self::$juzBoundaries)) {
            return [];
        }

        $b = self::$juzBoundaries[$idx];
        $ayahs = [];
        $surah = $b['start']['surah'];
        $ayah  = $b['start']['ayah'];
        $endSurah = $b['end']['surah'];
        $endAyah  = $b['end']['ayah'];

        while ($surah < $endSurah || ($surah === $endSurah && $ayah <= $endAyah)) {
            $ayahs[] = ['surah' => $surah, 'ayah' => $ayah, 'juz' => $juzNo];
            $max = self::$surahAyahCounts[$surah - 1];
            if ($ayah >= $max) {
                $surah++;
                $ayah = 1;
            } else {
                $ayah++;
            }
        }

        return $ayahs;
    }

    /**
     * All ayahs in Quran reading order.
     *
     * @return list<array{surah:int,ayah:int,juz:int}>
     */
    public static function allAyahsOrdered(): array
    {
        $all = [];
        for ($j = 1; $j <= 30; $j++) {
            foreach (self::ayahsInJuz($j) as $a) {
                $all[] = $a;
            }
        }

        return $all;
    }
}
