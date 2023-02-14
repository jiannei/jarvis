<?php

namespace App\Models;

use App\Support\Traits\SerializeDate;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    use SerializeDate;
}
