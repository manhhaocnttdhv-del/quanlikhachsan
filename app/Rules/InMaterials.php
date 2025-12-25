<?php

namespace App\Rules;

use Closure;
use App\Models\Material;
use Illuminate\Contracts\Validation\Rule;

class InMaterials implements Rule
{

    public function passes($attribute, $value)
    {
        return Material::exists();
    }

    public function message()
    {
        return 'Giá trị không nằm trong danh sách vật liệu.';
    }
}
