<?php

namespace App\Exports\Categories;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MainCategory implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Category::where('status', 1)->select('id','name','handling_fee')->get();
    }

    public function headings(): array
    {
        return ['id','name','handling_fee'];
    }
}
