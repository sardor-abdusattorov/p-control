<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute qabul qilinishi kerak.',
    'accepted_if' => ':other :value bo\'lganda :attribute qabul qilinishi kerak.',
    'active_url' => ':attribute haqiqiy URL emas.',
    'after' => ':attribute :date dan keyingi sana bo\'lishi kerak.',
    'after_or_equal' => ':attribute :date dan keyin yoki teng bo\'lishi kerak.',
    'alpha' => ':attribute faqat harflardan iborat bo\'lishi kerak.',
    'alpha_dash' => ':attribute faqat harflar, raqamlar, chiziqlar va pastki chiziqlardan iborat bo\'lishi kerak.',
    'alpha_num' => ':attribute faqat harflar va raqamlardan iborat bo\'lishi kerak.',
    'array' => ':attribute massiv bo\'lishi kerak.',
    'ascii' => ':attribute faqat bir baytli alfanumerik belgilar va ramzlardan iborat bo\'lishi kerak.',
    'before' => ':attribute :date dan oldin bo\'lishi kerak.',
    'before_or_equal' => ':attribute :date dan oldin yoki teng bo\'lishi kerak.',
    'between' => [
        'array' => ':attribute :min va :max ta elementga ega bo\'lishi kerak.',
        'file' => ':attribute :min dan :max kilobaytgacha bo\'lishi kerak.',
        'numeric' => ':attribute :min dan :max gacha bo\'lishi kerak.',
        'string' => ':attribute :min dan :max ta belgigacha bo\'lishi kerak.',
    ],
    'boolean' => ':attribute maydoni haqiqat yoki yolg\'on bo\'lishi kerak.',
    'confirmed' => ':attribute tasdiqlash mos kelmaydi.',
    'current_password' => 'Parol noto\'g\'ri.',
    'date' => ':attribute haqiqiy sana emas.',
    'date_equals' => ':attribute :date ga teng bo\'lishi kerak.',
    'date_format' => ':attribute format :format ga mos kelmaydi.',
    'declined' => ':attribute rad etilishi kerak.',
    'declined_if' => ':other :value bo\'lganda :attribute rad etilishi kerak.',
    'different' => ':attribute va :other farqli bo\'lishi kerak.',
    'digits' => ':attribute :digits raqamlardan iborat bo\'lishi kerak.',
    'digits_between' => ':attribute :min va :max raqamlardan iborat bo\'lishi kerak.',
    'dimensions' => ':attribute da noto\'g\'ri rasm o\'lchamlari mavjud.',
    'distinct' => ':attribute maydonida takroriy qiymat mavjud.',
    'doesnt_end_with' => ':attribute quyidagilardan biri bilan tugamasligi kerak: :values.',
    'doesnt_start_with' => ':attribute quyidagilardan biri bilan boshlanmasligi kerak: :values.',
    'email' => ':attribute haqiqiy email manzili bo\'lishi kerak.',
    'ends_with' => ':attribute quyidagi belgilar bilan tugashi kerak: :values.',
    'enum' => 'Tanlangan :attribute noto\'g\'ri.',
    'exists' => 'Tanlangan :attribute noto\'g\'ri.',
    'file' => ':attribute fayl bo\'lishi kerak.',
    'filled' => ':attribute maydonida qiymat bo\'lishi kerak.',
    'gt' => [
        'array' => ':attribute :value dan ko\'proq elementga ega bo\'lishi kerak.',
        'file' => ':attribute :value kilobaytdan katta bo\'lishi kerak.',
        'numeric' => ':attribute :value dan katta bo\'lishi kerak.',
        'string' => ':attribute :value dan ko\'proq belgiga ega bo\'lishi kerak.',
    ],
    'gte' => [
        'array' => ':attribute :value yoki undan ko\'proq elementga ega bo\'lishi kerak.',
        'file' => ':attribute :value kilobaytdan katta yoki teng bo\'lishi kerak.',
        'numeric' => ':attribute :value dan katta yoki teng bo\'lishi kerak.',
        'string' => ':attribute :value dan katta yoki teng bo\'lishi kerak.',
    ],
    'image' => ':attribute rasm bo\'lishi kerak.',
    'in' => 'Tanlangan :attribute noto\'g\'ri.',
    'in_array' => ':attribute maydoni :other da mavjud emas.',
    'integer' => ':attribute butun raqam bo\'lishi kerak.',
    'ip' => ':attribute haqiqiy IP manzili bo\'lishi kerak.',
    'ipv4' => ':attribute haqiqiy IPv4 manzili bo\'lishi kerak.',
    'ipv6' => ':attribute haqiqiy IPv6 manzili bo\'lishi kerak.',
    'json' => ':attribute haqiqiy JSON satri bo\'lishi kerak.',
    'lowercase' => ':attribute kichik harflarda bo\'lishi kerak.',
    'lt' => [
        'array' => ':attribute :value dan kam elementga ega bo\'lishi kerak.',
        'file' => ':attribute :value kilobaytdan kam bo\'lishi kerak.',
        'numeric' => ':attribute :value dan kam bo\'lishi kerak.',
        'string' => ':attribute :value dan kam belgiga ega bo\'lishi kerak.',
    ],
    'lte' => [
        'array' => ':attribute :value dan ko\'proq elementga ega bo\'lmasligi kerak.',
        'file' => ':attribute :value kilobaytdan kam yoki teng bo\'lishi kerak.',
        'numeric' => ':attribute :value dan kam yoki teng bo\'lishi kerak.',
        'string' => ':attribute :value dan kam yoki teng bo\'lishi kerak.',
    ],
    'mac_address' => ':attribute haqiqiy MAC manzili bo\'lishi kerak.',
    'max' => [
        'array' => ':attribute :max dan ko\'proq elementga ega bo\'lmasligi kerak.',
        'file' => ':attribute :max kilobaytdan katta bo\'lmasligi kerak.',
        'numeric' => ':attribute :max dan katta bo\'lmasligi kerak.',
        'string' => ':attribute :max dan ko\'proq belgiga ega bo\'lmasligi kerak.',
    ],
    'max_digits' => ':attribute :max dan ko\'proq raqamga ega bo\'lmasligi kerak.',
    'mimes' => ':attribute quyidagi turdagi fayl bo\'lishi kerak: :values.',
    'mimetypes' => ':attribute quyidagi turdagi fayl bo\'lishi kerak: :values.',
    'min' => [
        'array' => ':attribute kamida :min ta elementga ega bo\'lishi kerak.',
        'file' => ':attribute kamida :min kilobaytda bo\'lishi kerak.',
        'numeric' => ':attribute kamida :min bo\'lishi kerak.',
        'string' => ':attribute kamida :min belgidan iborat bo\'lishi kerak.',
    ],
    'min_digits' => ':attribute kamida :min ta raqamga ega bo\'lishi kerak.',
    'multiple_of' => ':attribute :value ning ko\'paytmasi bo\'lishi kerak.',
    'not_in' => 'Tanlangan :attribute noto\'g\'ri.',
    'not_regex' => ':attribute formati noto\'g\'ri.',
    'numeric' => ':attribute raqam bo\'lishi kerak.',
    'password' => [
        'letters' => ':attribute kamida bitta harfga ega bo\'lishi kerak.',
        'mixed' => ':attribute kamida bitta katta va bitta kichik harfga ega bo\'lishi kerak.',
        'numbers' => ':attribute kamida bitta raqamga ega bo\'lishi kerak.',
        'symbols' => ':attribute kamida bitta ramzga ega bo\'lishi kerak.',
        'uncompromised' => 'Berilgan :attribute ma\'lumotlar oqishida paydo bo\'lgan. Iltimos, boshqa bir :attribute tanlang.',
    ],
    'present' => ':attribute maydoni mavjud bo\'lishi kerak.',
    'prohibited' => ':attribute maydoni taqiqlangan.',
    'prohibited_if' => ':other :value bo\'lganda :attribute maydoni taqiqlangan.',
    'prohibited_unless' => ':other :values da bo\'lmagan taqdirda :attribute maydoni taqiqlangan.',
    'prohibits' => ':attribute maydoni :other ni mavjud bo\'lishiga taqiqlaydi.',
    'regex' => ':attribute formati noto\'g\'ri.',
    'required' => ':attribute maydoni majburiy.',
    'required_array_keys' => ':attribute maydoni quyidagi kalitlar uchun elementlarga ega bo\'lishi kerak: :values.',
    'required_if' => ':other :value bo\'lganda :attribute maydoni majburiy.',
    'required_if_accepted' => ':other qabul qilinganida :attribute maydoni majburiy.',
    'required_unless' => ':other :values da bo\'lmasa :attribute maydoni majburiy.',
    'required_with' => ':values mavjud bo\'lganda :attribute maydoni majburiy.',
    'required_with_all' => ':values mavjud bo\'lganda :attribute maydoni majburiy.',
    'required_without' => ':values mavjud bo\'lmasa :attribute maydoni majburiy.',
    'required_without_all' => ':values ning hech biri mavjud bo\'lmasa :attribute maydoni majburiy.',
    'same' => ':attribute va :other mos kelishi kerak.',
    'size' => [
        'array' => ':attribute :size ta elementga ega bo\'lishi kerak.',
        'file' => ':attribute :size kilobayt bo\'lishi kerak.',
        'numeric' => ':attribute :size bo\'lishi kerak.',
        'string' => ':attribute :size belgidan iborat bo\'lishi kerak.',
    ],
    'starts_with' => ':attribute quyidagi belgilar bilan boshlanishi kerak: :values.',
    'timezone' => ':attribute haqiqiy vaqt zonasini ifodalashi kerak.',
    'unique' => ':attribute allaqachon olingan.',
    'uploaded' => ':attribute yuklashda xato yuz berdi.',
    'url' => ':attribute formati noto\'g\'ri.',
    'uuid' => ':attribute haqiqiy UUID bo\'lishi kerak.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
