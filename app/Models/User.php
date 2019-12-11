<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function item()
    {
        return $this->hasMany(Item::class);
    }

    public function addCategory(Category $category)
    {
        return $this->categories()->save($category);
    }

    public function deleteCategory($categoryId)
    {
        $this->categories()->find($categoryId)->delete();
        return ["message" => "the shopping list has been deleted"];
    }

    public function hasDuplicateCategory($categoryName)
    {
        return $this->categories()->where("name",$categoryName)->count();
    }

    public function addItem(Item $item, $category_id)
    {
        return $this->categories()->find($category_id)->items()->create([
            'name' => $item->name,
            'description' => $item->description,
            'user_id' => $this->id
        ]);
    }

    public function hasDuplicateItem($category_id,$itemName)
    {
        return $this->categories()->find($category_id)->items()->where('name' , $itemName)->count();
    }
}
