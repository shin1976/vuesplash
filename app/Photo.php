<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
  /** プライマリキーの型 */
    protected $keyType = 'string';
    const ID_LENGTH = 12;
    
    protected $appends = [
      'url',
    ];
    protected $visible = [
      'id', 'owner', 'url',
  ];

    /** IDの桁数 */

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (! data_get($this->attributes, 'id')) {
            $this->setId();
        }
    }

    /**
     * ランダムなID値をid属性に代入する
     */
    private function setId()
    {
        $this->attributes['id'] = $this->getRandomId();
    }

    /**
     * ランダムなID値を生成する
     * @return string
     */
    private function getRandomId()
    {
        $characters = array_merge(
            range(0, 9), range('a', 'z'),
            range('A', 'Z'), ['-', '_']
        );

        $length = count($characters);

        $id = "";

        for ($i = 0; $i < self::ID_LENGTH; $i++) {
            $id .= $characters[random_int(0, $length - 1)];
        }

        return $id;
    }

    public function getUrlAttribute()
    {
      return Storage::cloud()->url($this->attributes['filename']);
    }






    public function owner()
    {
      return $this->belongsTo('App\User', 'user_id', 'id', 'users');
    }

  }
