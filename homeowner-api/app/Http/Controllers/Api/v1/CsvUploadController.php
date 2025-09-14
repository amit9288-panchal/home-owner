<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadCsvRequest;
use App\Http\Resources\PersonResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Person;
use App\Services\HomeownerParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CsvUploadController extends Controller
{
    use ResponseTrait;

    public function __construct(private HomeownerParser $parser)
    {
    }

    public function upload(UploadCsvRequest $request): JsonResponse
    {
        try {
            $file = fopen($request->file('csv')->getRealPath(), 'r');
            fgetcsv($file); 

            while (($row = fgetcsv($file)) !== false) {
                foreach ($this->parser->parse($row[0]) as $personData) {
                    Person::create($personData);
                    Log::info('Person parsed', $personData);
                }
            }

            fclose($file);

            return $this->successResponse([], 'CSV processed successfully');
        } catch (\Throwable $e) {
            Log::error('CSV upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse([], 'CSV upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $query = Person::query();

        if ($search = $request->input('search')) {
            $query->where('last_name', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%");
        }

        $paginated = $query->latest()->paginate(20);

        return $this->successResponse(
            PersonResource::collection($paginated),
            'People fetched'
        );
    }
}
