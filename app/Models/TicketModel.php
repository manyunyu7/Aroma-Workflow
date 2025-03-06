<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketModel extends Model
{
    protected $table = "tickets";
    use HasFactory;


    protected $append = ['operator', 'category_detail', 'nomor_ticket', 'status_desc'
        , 'duration_det'
    ];

    function getDurationDetAttribute()
    {
        if ($this->status == 1) {
            // Create two new DateTime-objects...
            $date1 = new DateTime($this->created_at);
            $date2 = new DateTime($this->durasi);

            // The diff-methods returns a new DateInterval-object...
            $diff = $date2->diff($date1);

            // Call the format method on the DateInterval-object
            return $diff->format('%a Day and %h hours %i minutes ');
        } else {
            $date1 = new DateTime($this->created_at);
            $date2 = new DateTime(date("Y-m-d h:i:s"));

            // The diff-methods returns a new DateInterval-object...
            $diff = $date2->diff($date1);

            // Call the format method on the DateInterval-object
            return $diff->format('%a Day and %h hours %i minutes ');

        }
    }

    function getStatusDescAttribute()
    {
        switch ($this->status) {
            case "3" :
                return "Pending";
                break;
            case "2":
                return "Diproses";
                break;
            case "1":
                return "Selesai";
                break;
            default:
                return "Dibatalkan";
        }
    }

    function getOperatorAttribute()
    {
        return User::find($this->delegate_id);
    }

    function getNomorTicketAttribute()
    {
        // Simply:
        $date = date('Ymd');
        // This would return the date in the following formats respectively:
//        $date = '2012-03-06 17:33:07';
        $ticketID = $this->id;
        return "NO$date$ticketID";
    }

    function getCategoryDetailAttribute()
    {
        return TicketCategory::find($this->category);
    }
}
