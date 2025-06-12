<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\Returning;
use App\Utility\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function main()
    {
        $userCount = User::count();
        $itemCount = Item::count();
        $categorycount = Category::count();
        $borrowingCount = Borrowing::count();
        $returningCount = Returning::count();

        return apiResponse::send(200, "Data retrieved", null,[
            "userCount" => $userCount,
            "itemCount" => $itemCount,
            "categoryCount"=> $categorycount,
            "borrowingCount" => $borrowingCount,
            "returningCount" => $returningCount,
        ]);
    }

    public function CategoryItemCount()
    {
        $categories =  Category::withCount('items')->get();

        return ApiResponse::send(200, 'Data retrived',null, $categories);
    }

    public function borrowing()
    {
        return apiResponse::send(200, "Data retrieved", null,[
            'borrowingStats' => [
                'total' => Borrowing::count(),
                'pending' => Borrowing::where('status', 'pending')->count(),
                'approved' => Borrowing::where('status', 'approved')->count(),
                'rejected' => Borrowing::where('status', 'rejected')->count(),
                'returned' => Borrowing::where('status', 'returned')->count(),
            ],
            'recentborrows' => 
                Borrowing::orderBy('created_at','desc')->with(["user", "item"])->get()->slice(0,5)
        ]);
    }

    public function borrowingByTime()
    {
        $items = Borrowing::all();

        if(request()->get('sort')=== 'today'){
            $startDate = Carbon::today();
            $items = Borrowing::where('created_at', $startDate)->get();
        }

        if(request()->get('sort')=== 'this_week'){
            $startDate = Carbon::now()->subWeek()->startOfWeek();
            $endDate = Carbon::now()->subWeek()->endOfWeek();
            $items = Borrowing::whereBetween('created_at', [$startDate, $endDate])->get();
        }

        if (request()->get('sort') === 'last5weeks') {
            $weeklyCounts = [];

            for ($i = 4; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

                $count = Borrowing::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();

                $formattedWeek = $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M');

                $weeklyCounts[] = [
                    'week' => $formattedWeek,
                    'total' => $count,
                ];
            }

            return ApiResponse::send(200, "Weekly data retrieved", null, [
                'per_week' => $weeklyCounts
            ]);
        }



        return ApiResponse::send(200, "Data retrived" , null ,$items);
    }

    public function returning()
    {
        return apiResponse::send(200, "Data retrieved", null,[
            'returningStats' => [
                'total' => Returning::count(),
                'approved' => Returning::whereNotNull('handled_by')->count(),
                'pending' => Returning::where('handled_by', null)->count(),
            ],
            'recentreturnings' => 
                Returning::whereNotNull('handled_by')->orderBy('created_at','desc')->with(['borrowing.user','borrowing.item','borrowing'])->get()->slice(0,5)
        ]);
    }

        public function overdue()
        {
            $today = Carbon::now();
            $query = Borrowing::with(["user","item"])->where('status', ['approved']);
            $data = $query->whereDate('due_date', '<' , $today)->orderBy('due_date', 'desc')->get();
            return ApiResponse::send(200, "Data retrived", null,[
                'dueStats'=> [
                    'overdueCount'=> $data->count()
                ],
                'overdueBorrows'=> $data->slice(0,3)
            ] );
        }
}
