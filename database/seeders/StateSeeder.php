<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        State::query()->truncate();
        City::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                'state' => ['en' => 'Cairo', 'ar' => 'القاهرة'],
                'cities' => [
                    ['en' => 'Cairo', 'ar' => 'القاهرة'],
                    ['en' => 'Nasr City', 'ar' => 'مدينة نصر'],
                    ['en' => 'Heliopolis', 'ar' => 'مصر الجديدة'],
                    ['en' => 'New Cairo', 'ar' => 'القاهرة الجديدة'],
                    ['en' => 'Maadi', 'ar' => 'المعادي'],
                    ['en' => 'Zamalek', 'ar' => 'الزمالك'],
                    ['en' => 'Shubra', 'ar' => 'شبرا'],
                    ['en' => '6th District (Cairo)', 'ar' => 'الحي السادس'],
                ],
            ],
            [
                'state' => ['en' => 'Giza', 'ar' => 'الجيزة'],
                'cities' => [
                    ['en' => 'Giza', 'ar' => 'الجيزة'],
                    ['en' => 'Dokki', 'ar' => 'الدقي'],
                    ['en' => 'Mohandessin', 'ar' => 'المهندسين'],
                    ['en' => '6th of October', 'ar' => 'السادس من أكتوبر'],
                    ['en' => 'Sheikh Zayed', 'ar' => 'الشيخ زايد'],
                    ['en' => 'Haram', 'ar' => 'الهرم'],
                    ['en' => 'Faisal', 'ar' => 'فيصل'],
                ],
            ],
            [
                'state' => ['en' => 'Alexandria', 'ar' => 'الإسكندرية'],
                'cities' => [
                    ['en' => 'Alexandria', 'ar' => 'الإسكندرية'],
                    ['en' => 'Borg El Arab', 'ar' => 'برج العرب'],
                    ['en' => 'Al Montazah', 'ar' => 'المنتزه'],
                    ['en' => 'Smouha', 'ar' => 'سموحة'],
                    ['en' => 'Stanley', 'ar' => 'ستانلي'],
                ],
            ],
            [
                'state' => ['en' => 'Dakahlia', 'ar' => 'الدقهلية'],
                'cities' => [
                    ['en' => 'Mansoura', 'ar' => 'المنصورة'],
                    ['en' => 'Talkha', 'ar' => 'طلخا'],
                    ['en' => 'Mit Ghamr', 'ar' => 'ميت غمر'],
                    ['en' => 'Sherbin', 'ar' => 'شربين'],
                    ['en' => 'Sinbillawin', 'ar' => 'سنبلاوين'],
                ],
            ],
            [
                'state' => ['en' => 'Sharqia', 'ar' => 'الشرقية'],
                'cities' => [
                    ['en' => 'Zagazig', 'ar' => 'الزقازيق'],
                    ['en' => '10th of Ramadan', 'ar' => 'العاشر من رمضان'],
                    ['en' => 'Belbeis', 'ar' => 'بلبيس'],
                    ['en' => 'Abu Kebir', 'ar' => 'أبو كبير'],
                ],
            ],
            [
                'state' => ['en' => 'Qalyubia', 'ar' => 'القليوبية'],
                'cities' => [
                    ['en' => 'Banha', 'ar' => 'بنها'],
                    ['en' => 'Qalyub', 'ar' => 'قليوب'],
                    ['en' => 'Shubra El-Kheima', 'ar' => 'شبرا الخيمة'],
                    ['en' => 'Toukh', 'ar' => 'طوخ'],
                ],
            ],
            [
                'state' => ['en' => 'Gharbia', 'ar' => 'الغربية'],
                'cities' => [
                    ['en' => 'Tanta', 'ar' => 'طنطا'],
                    ['en' => 'Mahalla', 'ar' => 'المحلة الكبرى'],
                    ['en' => 'Kafr El Zayat', 'ar' => 'كفر الزيات'],
                ],
            ],
            [
                'state' => ['en' => 'Monufia', 'ar' => 'المنوفية'],
                'cities' => [
                    ['en' => 'Shibin El Kom', 'ar' => 'شبين الكوم'],
                    ['en' => 'Sadat City', 'ar' => 'مدينة السادات'],
                    ['en' => 'Quesna', 'ar' => 'قويسنا'],
                ],
            ],
            [
                'state' => ['en' => 'Beheira', 'ar' => 'البحيرة'],
                'cities' => [
                    ['en' => 'Damanhur', 'ar' => 'دمنهور'],
                    ['en' => 'Kafr El Dawwar', 'ar' => 'كفر الدوار'],
                    ['en' => 'Rashid', 'ar' => 'رشيد'],
                ],
            ],
            [
                'state' => ['en' => 'Kafr El Sheikh', 'ar' => 'كفر الشيخ'],
                'cities' => [
                    ['en' => 'Kafr El Sheikh', 'ar' => 'كفر الشيخ'],
                    ['en' => 'Desouk', 'ar' => 'دسوق'],
                    ['en' => 'Sidi Salem', 'ar' => 'سيدي سالم'],
                ],
            ],
            [
                'state' => ['en' => 'Fayoum', 'ar' => 'الفيوم'],
                'cities' => [
                    ['en' => 'Fayoum', 'ar' => 'الفيوم'],
                    ['en' => 'Tamiya', 'ar' => 'طامية'],
                    ['en' => 'Ibshaway', 'ar' => 'إبشواي'],
                ],
            ],
            [
                'state' => ['en' => 'Beni Suef', 'ar' => 'بني سويف'],
                'cities' => [
                    ['en' => 'Beni Suef', 'ar' => 'بني سويف'],
                    ['en' => 'Al Wasta', 'ar' => 'الواسطة'],
                    ['en' => 'Nasser', 'ar' => 'ناصر'],
                ],
            ],
            [
                'state' => ['en' => 'Minya', 'ar' => 'المنيا'],
                'cities' => [
                    ['en' => 'Minya', 'ar' => 'المنيا'],
                    ['en' => 'Samalut', 'ar' => 'سمالوط'],
                    ['en' => 'Mallawi', 'ar' => 'ملوي'],
                ],
            ],
            [
                'state' => ['en' => 'Assiut', 'ar' => 'أسيوط'],
                'cities' => [
                    ['en' => 'Assiut', 'ar' => 'أسيوط'],
                    ['en' => 'Manfalut', 'ar' => 'منفلوط'],
                    ['en' => 'El Quseia', 'ar' => 'القوصية'],
                ],
            ],
            [
                'state' => ['en' => 'Sohag', 'ar' => 'سوهاج'],
                'cities' => [
                    ['en' => 'Sohag', 'ar' => 'سوهاج'],
                    ['en' => 'Tahta', 'ar' => 'طهطا'],
                    ['en' => 'Akhmim', 'ar' => 'أخميم'],
                ],
            ],
            [
                'state' => ['en' => 'Qena', 'ar' => 'قنا'],
                'cities' => [
                    ['en' => 'Qena', 'ar' => 'قنا'],
                    ['en' => 'Nag Hammadi', 'ar' => 'نجع حمادي'],
                    ['en' => 'Qus', 'ar' => 'قوص'],
                ],
            ],
            [
                'state' => ['en' => 'Luxor', 'ar' => 'الأقصر'],
                'cities' => [
                    ['en' => 'Luxor', 'ar' => 'الأقصر'],
                    ['en' => 'Armant', 'ar' => 'أرمنت'],
                    ['en' => 'Esna', 'ar' => 'إسنا'],
                ],
            ],
            [
                'state' => ['en' => 'Aswan', 'ar' => 'أسوان'],
                'cities' => [
                    ['en' => 'Aswan', 'ar' => 'أسوان'],
                    ['en' => 'Kom Ombo', 'ar' => 'كوم أمبو'],
                    ['en' => 'Edfu', 'ar' => 'إدفو'],
                ],
            ],
            [
                'state' => ['en' => 'Red Sea', 'ar' => 'البحر الأحمر'],
                'cities' => [
                    ['en' => 'Hurghada', 'ar' => 'الغردقة'],
                    ['en' => 'Safaga', 'ar' => 'سفاجا'],
                    ['en' => 'Quseir', 'ar' => 'القصير'],
                ],
            ],
            [
                'state' => ['en' => 'New Valley', 'ar' => 'الوادي الجديد'],
                'cities' => [
                    ['en' => 'Kharga', 'ar' => 'الخارجة'],
                    ['en' => 'Dakhla', 'ar' => 'الداخلة'],
                    ['en' => 'Farafra', 'ar' => 'الفرافرة'],
                ],
            ],
            [
                'state' => ['en' => 'Matrouh', 'ar' => 'مطروح'],
                'cities' => [
                    ['en' => 'Marsa Matruh', 'ar' => 'مرسى مطروح'],
                    ['en' => 'Siwa', 'ar' => 'سيوة'],
                    ['en' => 'El Alamein', 'ar' => 'العلمين'],
                ],
            ],
            [
                'state' => ['en' => 'North Sinai', 'ar' => 'شمال سيناء'],
                'cities' => [
                    ['en' => 'Arish', 'ar' => 'العريش'],
                    ['en' => 'Bir al-Abed', 'ar' => 'بئر العبد'],
                    ['en' => 'Rafah', 'ar' => 'رفح'],
                ],
            ],
            [
                'state' => ['en' => 'South Sinai', 'ar' => 'جنوب سيناء'],
                'cities' => [
                    ['en' => 'Sharm El Sheikh', 'ar' => 'شرم الشيخ'],
                    ['en' => 'Dahab', 'ar' => 'دهب'],
                    ['en' => 'Taba', 'ar' => 'طابا'],
                ],
            ],
            [
                'state' => ['en' => 'Port Said', 'ar' => 'بورسعيد'],
                'cities' => [
                    ['en' => 'Port Said', 'ar' => 'بورسعيد'],
                    ['en' => 'Port Fouad', 'ar' => 'بورفؤاد'],
                ],
            ],
            [
                'state' => ['en' => 'Suez', 'ar' => 'السويس'],
                'cities' => [
                    ['en' => 'Suez', 'ar' => 'السويس'],
                    ['en' => 'Ain Sokhna', 'ar' => 'العين السخنة'],
                ],
            ],
            [
                'state' => ['en' => 'Ismailia', 'ar' => 'الإسماعيلية'],
                'cities' => [
                    ['en' => 'Ismailia', 'ar' => 'الإسماعيلية'],
                    ['en' => 'Qantara', 'ar' => 'القنطرة'],
                ],
            ],
        ];

        foreach ($data as $item) {
            $state = State::query()->create([
                'name' => $item['state']
            ]);

            foreach ($item['cities'] as $city) {
                City::query()->create([
                    'name' => $city,
                    'state_id' => $state->id
                ]);
            }
        }
    }
}
