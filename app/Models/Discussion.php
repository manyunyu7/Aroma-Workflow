<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $append = ['user_detail','topic_detail'];
    
    function getUserDetailAttribute()
    {
        return User::find($this->id_sender);
    }
    
    function getTopicDetailAttribute()
    {
        return TicketModel::find($this->topic);
    }
}
