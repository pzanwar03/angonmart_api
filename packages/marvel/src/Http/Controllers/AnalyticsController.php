<?php

namespace Marvel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Marvel\Database\Models\Category;
use Marvel\Database\Models\Product;
use Marvel\Database\Models\Type;
use Marvel\Database\Models\User;
use Marvel\Database\Repositories\AddressRepository;
use Marvel\Enums\OrderStatus;
use Marvel\Enums\Permission;
use Marvel\Exceptions\MarvelException;
use Spatie\Permission\Models\Permission as ModelsPermission;

class AnalyticsController extends CoreController
{
    public $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }


    public function analytics(Request $request)
    {
        try {
            $user = $request->user();

            // Total revenue
            $totalRevenue = DB::table('orders')
                ->whereDate('created_at', '<=', Carbon::now())
                ->where('order_status', OrderStatus::COMPLETED)
                ->sum('paid_total');

            // Today's revenue
            $todaysRevenue = DB::table('orders')
                ->whereDate('created_at', '>', Carbon::now()->subDays(1))
                ->where('order_status', OrderStatus::COMPLETED)
                ->sum('paid_total');

            // total refunds
            $totalRefunds = DB::table('refunds')
                ->whereDate('created_at', '<', Carbon::now())
                ->sum('amount');

            // total orders
            $totalOrders = DB::table('orders')->whereDate('created_at', '<=', Carbon::now())->count();

            $newCustomers = User::permission(Permission::CUSTOMER)->whereDate('created_at', '>', Carbon::now()->subDays(30))->count();

            $totalYearSaleByMonth = $this->getTotalYearSaleByMonth($user);
            $todayTotalOrderByStatus = $this->orderCountingByStatus($request, 1);
            $weeklyDaysTotalOrderByStatus = $this->orderCountingByStatus($request, 7);
            $monthlyTotalOrderByStatus = $this->orderCountingByStatus($request, 30);
            $yearlyTotalOrderByStatus = $this->orderCountingByStatus($request, 365);


            return [
                'totalRevenue'              => $totalRevenue ?? 0,
                'totalRefunds'              => $totalRefunds ?? 0,
                'todaysRevenue'             => $todaysRevenue,
                'totalOrders'               => $totalOrders,
                'newCustomers'              => $newCustomers,
                'totalYearSaleByMonth'      => $totalYearSaleByMonth,
                'todayTotalOrderByStatus'   => $todayTotalOrderByStatus,
                'weeklyTotalOrderByStatus'  => $weeklyDaysTotalOrderByStatus,
                'monthlyTotalOrderByStatus' => $monthlyTotalOrderByStatus,
                'yearlyTotalOrderByStatus'  => $yearlyTotalOrderByStatus,
            ];
        } catch (MarvelException $e) {
            throw new MarvelException(SOMETHING_WENT_WRONG, $e->getMessage());
        }
    }

    public function getTotalYearSaleByMonth(User $user)
    {
        $months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        $totalYearSaleByMonth = DB::table('orders as A')
            ->where('A.order_status', OrderStatus::COMPLETED)
            ->whereYear('A.created_at', Carbon::now()->year)
            ->select(
                DB::raw("SUM(A.paid_total) as total"),
                DB::raw("DATE_FORMAT(A.created_at, '%M') as month")
            )
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return array_map(
            fn ($month) =>
            [
                'month' => $month,
                'total' => $totalYearSaleByMonth[$month] ?? 0
            ],
            $months
        );
    }

    public function orderCountingByStatus($request, int $days = 1)
    {
        $query = DB::table('orders as A')
            ->whereDate('A.created_at', '>', Carbon::now()->subDays($days))
            ->select(
                'A.order_status',
                DB::raw('count(*) as order_count')
            )
            ->groupBy('A.order_status')
            ->pluck('order_count', 'order_status');

        return [
            'pending'        => $query[OrderStatus::PENDING]           ?? 0,
            'processing'     => $query[OrderStatus::PROCESSING]        ?? 0,
            'complete'       => $query[OrderStatus::COMPLETED]         ?? 0,
            'cancelled'      => $query[OrderStatus::CANCELLED]         ?? 0,
            'refunded'       => $query[OrderStatus::REFUNDED]          ?? 0,
            'failed'         => $query[OrderStatus::FAILED]            ?? 0,
            'localFacility'  => $query[OrderStatus::AT_LOCAL_FACILITY] ?? 0,
            'outForDelivery' => $query[OrderStatus::OUT_FOR_DELIVERY]  ?? 0,
        ];
    }

    /**
     * lowStockProducts
     *
     * @param  Request $request
     * @return object
     */
    public function lowStockProducts(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        return $this->lowStockProductsWithPagination($request)->take($limit)->get();
    }

    /**
     * lowStockProducts
     *
     * @param  Request $request
     * @return object
     */
    public function lowStockProductsWithPagination(Request $request)
    {
        $language = $request->language ?? DEFAULT_LANGUAGE;

        // product group type
        $type_id = $request->type_id ? $request->type_id : '';
        if (isset($request->type_slug) && empty($type_id)) {
            try {
                $type = Type::where('slug', $request->type_slug)->where('language', $language)->firstOrFail();
                $type_id = $type->id;
            } catch (MarvelException $e) {
                throw new MarvelException(NOT_FOUND);
            }
        }
        $products_query = Product::with(['type'])->where('language', $language)->where('quantity', '<', 10);

        // fetched type
        if ($type_id) {
            $products_query = $products_query->where('type_id', '=', $type_id);
        }
        return $products_query;
    }

    /**
     * categoryWiseProduct
     *
     * @param  Request $request
     * @return void
     */
    public function categoryWiseProduct(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        $language = $request->language ? $request->language : DEFAULT_LANGUAGE;

        return DB::table('category_product')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('COUNT(category_product.product_id) as product_count')
            )
            ->where('categories.language', '=', $language)
            ->join('products', 'category_product.product_id', '=', 'products.id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('product_count', 'DESC')
            ->limit($limit)
            ->get();
    }
    /**
     * categoryWiseProductSale
     *
     * @param  Request $request
     * @return void
     */
    public function categoryWiseProductSale(Request $request)
    {
        $limit = $request->limit ? $request->limit : 15;
        $language = $request->language ? $request->language : DEFAULT_LANGUAGE;

        return DB::table('categories')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('sum(order_product.order_quantity) as total_sales')
            )
            ->leftJoin('category_product', 'category_product.category_id', '=', 'categories.id')
            ->leftJoin('products', 'category_product.product_id', '=', 'products.id')
            ->leftJoin('order_product', 'order_product.product_id', '=', 'products.id')
            ->leftJoin('orders', 'order_product.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'order-completed')
            ->where('categories.language', '=', $language)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->limit($limit)
            ->get();
    }


    /**
     * topRatedProducts
     *
     * @param  Request $request
     * @return void
     */
    public function topRatedProducts(Request $request)
    {
        $limit = $request->limit ? $request->limit : 10;
        $language = $request->language ? $request->language : DEFAULT_LANGUAGE;

        $topRatedProducts = DB::table('reviews')
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->join('types', 'types.id', '=', 'products.type_id')
            ->select(
                'products.id as id',
                'products.name as name',
                'products.slug as slug',
                'products.price as regular_price',
                'products.sale_price as sale_price',
                'products.min_price as min_price',
                'products.max_price as max_price',
                'products.product_type as product_type',
                'products.description as description',
                'types.id as type_id',
                'types.slug as type_slug',
                DB::raw('JSON_UNQUOTE(products.image) AS image_json'),
                DB::raw('SUM(reviews.rating) as total_rating'),
                DB::raw('COUNT(reviews.id) as rating_count'),
                DB::raw('SUM(reviews.rating) / COUNT(reviews.id) as actual_rating'),
            )
            ->where('products.language', '=', $language)
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'products.price',
                'products.sale_price',
                'products.min_price',
                'products.max_price',
                'products.product_type',
                'products.description',
                'products.image',
                'types.id',
                'types.slug'
            )
            ->orderBy('actual_rating', 'desc')
            ->limit($limit)
            ->get();

        foreach ($topRatedProducts as $row) {
            $row->image = json_decode($row->image_json, true);
            unset($row->image_json);
        }

        return $topRatedProducts;
    }
}
