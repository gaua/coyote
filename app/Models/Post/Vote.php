<?php

namespace Coyote\Post;

use Coyote\Models\Scopes\ForUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

/**
 * @property int $post_id
 * @property int $user_id
 * @property int $forum_id
 * @property string $ip
 */
class Vote extends Model
{
    use ForUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['post_id', 'user_id', 'forum_id', 'ip'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post_votes';

    /**
     * @var array
     */
    public $timestamps = false;
}
