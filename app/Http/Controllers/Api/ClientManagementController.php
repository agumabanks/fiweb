<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\UserLoan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\CentralLogics\Helpers;
use App\Models\Guarantor;



use App\Models\LoanPaymentInstallment;

use App\Models\SanaaCard;
use Illuminate\Support\Facades\DB;
use PDF;

use App\Models\LoanPayment;

use Carbon\Carbon;



class ClientManagementController extends Controller
{
    // 1. List all clients
    public function index(): JsonResponse
    {
        $clients = Client::all();
        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }






    // 2. Search and paginate clients
    public function getClients2222(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $clientsQuery = Client::query();

        if ($search) {
            $key = explode(' ', $search);
            $clientsQuery->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhere('nin', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
        }

        $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit());
        $clientsData = $clients->map(function ($client) {
            $addedByUser = User::find($client->added_by);
            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'nin' => $client->nin,
                'created_at' => $client->created_at,
                'added_by' => $addedByUser ? $addedByUser->f_name . ' ' . $addedByUser->l_name : 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $clientsData,
            'pagination' => [
                'total' => $clients->total(),
                'count' => $clients->count(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'total_pages' => $clients->lastPage(),
            ]
        ]);
    }


public function getClients(Request $request): JsonResponse
{
    // Initialize search and query parameters
    $search = $request->get('search');
    $clientsQuery = Client::query();

    // Apply search filter if it exists
    if ($search) {
        $key = explode(' ', $search);
        $clientsQuery->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('name', 'like', "%{$value}%")
                  ->orWhere('nin', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%")
                  ->orWhere('phone', 'like', "%{$value}%");
            }
        });
    }

    // Paginate clients
    $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit());

    // Prepare response data
    $clientsData = $clients->map(function ($client) {
        $addedByUser = User::find($client->added_by);
        return [
            'client' => $client,
            'added_by_name' => $addedByUser ? $addedByUser->f_name . ' ' . $addedByUser->l_name : 'N/A',
        ];
    });

    // Return a JSON response with pagination
    return response()->json([
        'success' => true,
        'data' => $clientsData,
        'pagination' => [
            'total' => $clients->total(),
            'count' => $clients->count(),
            'per_page' => $clients->perPage(),
            'current_page' => $clients->currentPage(),
            'total_pages' => $clients->lastPage(),
        ],
    ]);
}




    // 3. Create a new client
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:clients',
            'phone' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'credit_balance' => 'required|numeric|min:0',
            'savings_balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        // Generate email if not provided
        if (empty($request->email)) {
            $request->merge([
                'email' => strtolower(str_replace(' ', '', $request->name)) . '@sanaa.co',
            ]);
        }

        $client = Client::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data' => $client,
        ], 201);
    }

    // 4. Get client details by ID
    public function show121212121($id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }
    
    
    
    
    
    
    public function show($id) : JsonResponse
        {
            // Fetch the client by ID
            $client = Client::findOrFail($id);
            
            // Get the agent who added the client
            $agent = User::find($client->added_by);
            
            // Get all guarantors associated with this client
            $guarantors = Guarantor::where('client_id', $client->id)->get();
            
            // Get client loan payment history
            $clientLoanPayHistory = LoanPayment::where('client_id', $client->id)
                                    ->orderBy('created_at', 'desc') // Order by paid_at in descending order (latest first)
                                    ->get();
            
            // Get client loans
            $clientLoans = UserLoan::where('client_id', $client->id)->get();
        
            // Prepare the response data
            $responseData = [
                'client' => $client,
                'agent' => $agent,
                'guarantors' => $guarantors,
                'loan_payment_history' => $clientLoanPayHistory,
                'client_loans' => $clientLoans
            ];
        
            // Return a JSON response with all data
            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        }

    
    
    

    // 5. Update client details
    public function update(Request $request, $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:clients,email,' . $id,
            'phone' => 'sometimes|required|string|max:20|unique:clients,phone,' . $id,
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'kyc_verified_at' => 'nullable|date',
            'dob' => 'nullable|date',
            'business' => 'nullable|string|max:255',
            'nin' => 'nullable|string|max:255',
            'credit_balance' => 'nullable|numeric|min:0',
            'savings_balance' => 'nullable|numeric|min:0',
            'next_of_kin' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 400);
        }

        $client->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            'data' => $client,
        ]);
    }

    // 6. Delete client
    public function destroy($id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully',
        ]);
    }

    // 7. Search clients by name or email
    public function search(Request $request): JsonResponse
    {
        $search = $request->query('q');

        $clients = Client::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }

    // 8. Fetch client loans by client ID
    public function clientLoans($id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        $loans = UserLoan::where('client_id', $id)->get();

        return response()->json([
            'success' => true,
            'data' => $loans,
        ]);
    }

    // 9. Upload Client Photo
    public function uploadPhoto(Request $request, $id): JsonResponse
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
            ], 404);
        }

        if ($request->hasFile('client_photo')) {
            $photo = $request->file('client_photo')->store('clients/photos', 'public');
            $client->client_photo = $photo;
            $client->save();

            return response()->json([
                'success' => true,
                'message' => 'Client photo uploaded successfully',
                'data' => $client,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No photo uploaded',
        ], 400);
    }
}

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Client;
// use App\Models\UserLoan;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;
// use App\Models\User;
// use App\CentralLogics\Helpers;

// class ClientManagementController extends Controller
// {
//     // 1. List all clients
//     public function index(): JsonResponse
//     {
//         $clients = Client::all();
//         return response()->json([
//             'success' => true,
//             'data' => $clients
//         ]);
//     }

//     // 2. Search and paginate clients
//     public function getClients(Request $request): JsonResponse
//     {
//         $search = $request->get('search');
//         $clientsQuery = Client::query();

//         if ($search) {
//             $key = explode(' ', $search);
//             $clientsQuery->where(function ($q) use ($key) {
//                 foreach ($key as $value) {
//                     $q->orWhere('name', 'like', "%{$value}%")
//                         ->orWhere('nin', 'like', "%{$value}%")
//                         ->orWhere('email', 'like', "%{$value}%")
//                         ->orWhere('phone', 'like', "%{$value}%");
//                 }
//             });
//         }

//         $clients = $clientsQuery->latest()->paginate(Helpers::pagination_limit());
//         $clientsData = $clients->map(function ($client) {
//             $addedByUser = User::find($client->added_by);
//             return [
//                 'id' => $client->id,
//                 'name' => $client->name,
//                 'email' => $client->email,
//                 'phone' => $client->phone,
//                 'nin' => $client->nin,
//                 'created_at' => $client->created_at,
//                 'added_by' => $addedByUser ? $addedByUser->f_name . ' ' . $addedByUser->l_name : 'N/A',
//             ];
//         });

//         return response()->json([
//             'success' => true,
//             'data' => $clientsData,
//             'pagination' => [
//                 'total' => $clients->total(),
//                 'count' => $clients->count(),
//                 'per_page' => $clients->perPage(),
//                 'current_page' => $clients->currentPage(),
//                 'total_pages' => $clients->lastPage(),
//             ]
//         ]);
//     }

//     // 3. Create a new client
//     public function store(Request $request): JsonResponse
//     {
//         $validator = Validator::make($request->all(), [
//             'name' => 'required|string|max:255',
//             'email' => 'nullable|string|email|max:255|unique:clients',
//             'phone' => 'required|string|max:255',
//             'status' => 'required|string|max:255',
//             'credit_balance' => 'required|numeric|min:0',
//             'savings_balance' => 'required|numeric|min:0',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'errors' => $validator->errors(),
//             ], 400);
//         }

//         // Generate email if not provided
//         if (empty($request->email)) {
//             $request->merge([
//                 'email' => strtolower(str_replace(' ', '', $request->name)) . '@sanaa.co',
//             ]);
//         }

//         $client = Client::create($request->all());

//         return response()->json([
//             'success' => true,
//             'message' => 'Client created successfully',
//             'data' => $client,
//         ], 201);
//     }

//     // 4. Get client details by ID
//     public function show($id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found'
//             ], 404);
//         }

//         return response()->json([
//             'success' => true,
//             'data' => $client
//         ]);
//     }

//     // 5. Update client details
//     public function update(Request $request, $id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found'
//             ], 404);
//         }

//         $validator = Validator::make($request->all(), [
//             'name' => 'sometimes|required|string|max:255',
//             'email' => 'sometimes|required|email|unique:clients,email,' . $id,
//             'phone' => 'sometimes|required|string|max:20|unique:clients,phone,' . $id,
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'errors' => $validator->errors()
//             ], 400);
//         }

//         $client->update($request->all());

//         return response()->json([
//             'success' => true,
//             'message' => 'Client updated successfully',
//             'data' => $client
//         ]);
//     }

//     // 6. Delete client
//     public function destroy($id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found'
//             ], 404);
//         }

//         $client->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Client deleted successfully'
//         ]);
//     }

//     // 7. Fetch client loans by client ID
//     public function clientLoans($id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found'
//             ], 404);
//         }

//         $loans = UserLoan::where('client_id', $id)->get();

//         return response()->json([
//             'success' => true,
//             'data' => $loans
//         ]);
//     }
    
    
    
//      // 5. Update client details
//     public function update(Request $request, $id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found',
//             ], 404);
//         }

//         $validator = Validator::make($request->all(), [
//             'name' => 'sometimes|required|string|max:255',
//             'email' => 'sometimes|required|email|unique:clients,email,' . $id,
//             'phone' => 'sometimes|required|string|max:20|unique:clients,phone,' . $id,
//             'address' => 'nullable|string|max:255',
//             'status' => 'nullable|string|max:255',
//             'kyc_verified_at' => 'nullable|date',
//             'dob' => 'nullable|date',
//             'business' => 'nullable|string|max:255',
//             'nin' => 'nullable|string|max:255',
//             'credit_balance' => 'nullable|numeric|min:0',
//             'savings_balance' => 'nullable|numeric|min:0',
//             'next_of_kin' => 'nullable|string|max:255',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'errors' => $validator->errors(),
//             ], 400);
//         }

//         $client->update($request->all());

//         return response()->json([
//             'success' => true,
//             'message' => 'Client updated successfully',
//             'data' => $client,
//         ]);
//     }

//     // 6. Delete client
//     public function destroy($id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found',
//             ], 404);
//         }

//         $client->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Client deleted successfully',
//         ]);
//     }

//     // 7. Search clients by name or email
//     public function search(Request $request): JsonResponse
//     {
//         $search = $request->query('q');

//         $clients = Client::where('name', 'LIKE', "%{$search}%")
//             ->orWhere('email', 'LIKE', "%{$search}%")
//             ->get();

//         return response()->json([
//             'success' => true,
//             'data' => $clients,
//         ]);
//     }

//     // 8. Fetch client loans by client ID
//     public function clientLoans($id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found',
//             ], 404);
//         }

//         $loans = UserLoan::where('client_id', $id)->get();

//         return response()->json([
//             'success' => true,
//             'data' => $loans,
//         ]);
//     }

//     // 9. Additional functionality: Client loan history
//     public function loanHistory($id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found',
//             ], 404);
//         }

//         $loanHistory = UserLoan::where('client_id', $id)
//             ->orderBy('created_at', 'desc')
//             ->get();

//         return response()->json([
//             'success' => true,
//             'data' => $loanHistory,
//         ]);
//     }

//     // 10. Upload Client Photo
//     public function uploadPhoto(Request $request, $id): JsonResponse
//     {
//         $client = Client::find($id);

//         if (!$client) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Client not found',
//             ], 404);
//         }

//         if ($request->hasFile('client_photo')) {
//             $photo = $request->file('client_photo')->store('clients/photos', 'public');
//             $client->client_photo = $photo;
//             $client->save();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Client photo uploaded successfully',
//                 'data' => $client,
//             ]);
//         }

//         return response()->json([
//             'success' => false,
//             'message' => 'No photo uploaded',
//         ], 400);
//     }
// }


