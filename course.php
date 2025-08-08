<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Str;
use Inertia\Inertia;
use App\Support\Storage\FocusFlowApiClient;
use Carbon\Carbon;

class CourseController extends Controller
{
    public function index(Request $request)
    {
     $user_uuid = $request->user()->uuid;
        try {
            // Make a GET request to the external API with proper headers
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get('http://ec2-13-60-10-23.eu-north-1.compute.amazonaws.com/api/v1/courses/'.$user_uuid);

            if ($response->successful()) {
                $courses = $response->json();

                // Pass courses data to the view using Inertia
                return Inertia::render('courses/courses', [
                    'courses' => $courses
                ]);
            } else {
                // If the API request failed, log the error and return an empty array
                Log::error('Failed to fetch courses: ' . $response->status());
                return Inertia::render('courses/courses', [
                    'courses' => [],
                    'error' => 'Failed to load courses. Please try again later.'
                ]);
            }
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error('Exception during courses API request: ' . $e->getMessage());
            return Inertia::render('courses/courses', [
                'courses' => [],
                'error' => 'An error occurred while loading courses.'
            ]);
        }
    }
    public function detail(Request $request)
    {
        try {
            // Get course ID from request
            $courseId = $request->query('id');



            if (!$courseId) {
                return redirect()->route('courses.index');
            }

            // fettch course details from the API
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get('http://ec2-13-60-10-23.eu-north-1.compute.amazonaws.com/api/v1/courses/details/'.$courseId);
            if ($response->failed()) {
                // If the API request failed, log the error and redirect to the courses index
                Log::error('Failed to fetch course details: ' . $response->status());
                return redirect()->route('courses.index')
                    ->with('error', 'Failed to load course details. Please try again later.');
            }
            $course = $response->json();


            // For now, we'll render the page without fetching actual data

            // The demo data is hardcoded in the Vue component
            return Inertia::render('courses/courseDetail', [
                'course' => $course
            ]);



        } catch (\Exception $e) {
            // Log any exceptions
            Log::error('Exception during course detail API request: ' . $e->getMessage());
            return redirect()->route('courses.index')
                ->with('error', 'An error occurred while loading the course details.');
        }
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'uuid' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB file
            'prerequisite' => 'nullable|string',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'message' => 'nullable|string',
            'difficulty' => 'nullable|string',
            'progress' => 'nullable|numeric',
            'categories' => 'required|array|min:1',
            'is_draft' => 'nullable|boolean',
            'user_uuids' => 'nullable|array',
        ]);

        // Get the UUID of the auth user
        $user = $request->user();
        $auth_uuid = $user->uuid;

        // Push the auth UUID to the user_uuids array
        if (!isset($validated['user_uuids'])) {
            $validated['user_uuids'] = [];
        }
        $validated['user_uuids'][] = $auth_uuid;

        // Handle image upload to S3 if file is present
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Generate a unique filename with course UUID
            $filename = $validated['uuid'] . '-' . time() . '.' . $file->getClientOriginalExtension();

            // Upload to S3 and get the URL
            $path = Storage::disk('s3')->put('courses/' . $filename, file_get_contents($file), 'public');
            $imageUrl = Storage::disk('s3')->url('courses/' . $filename);

            // Replace the file object with the S3 URL in the validated data
            $validated['image'] = $imageUrl;

            Log::info('Image uploaded to S3', ['url' => $imageUrl]);
        } else {
            // If no image provided, use a placeholder
            $validated['image'] = 'https://picsum.photos/800/450?random=' . rand(1, 1000);
        }

        Log::info('Course data prepared', ['data' => $validated]);

        // dd($validated);


        ///make request to api to save the data
        try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('http://ec2-13-60-10-23.eu-north-1.compute.amazonaws.com/api/v1/courses', $validated);
            if ($response->successful()) {

         //redirect the course course content page
            return redirect()->route('courses.content', ['course_id' => $validated['uuid']]);
            }
        } catch (\Exception $e) {
            Log::error('Error saving course:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save course');
        }


    }


    /**
     * Store course content (modules and lessons)
     */
    public function storeContent(Request $request)
    {

        //make a request to the save with the data

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("http://ec2-13-60-10-23.eu-north-1.compute.amazonaws.com/api/v1/modules", $request->all());

            if ($response->successful()) {


                //redirect to the course  page with success message
                $request->session()->flash('success', 'Course settings updated successfully');
                return redirect()->route('courses.settings', ['course_id' => $request->course_id]);
            }

        } catch (\Exception $e) {

            return redirect()->route('courses.settings', ['course_id' => $validated['course_id']]);
        }

    }

    /**
     * Store course settings (pricing, enrollment options, etc.)
     */
    public function storeSettings(Request $request)
    {
        //log the whole request dd($request->all());

         $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'prerequisite' => 'nullable|string',
            'course_id' => 'required|string',
            'completion_message' => 'nullable|string',
            'difficulty_level' => 'nullable|string',
        ]);


      // call the api to update the settings


            // Try both formats - first with the structured data format
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->put("http://ec2-13-60-10-23.eu-north-1.compute.amazonaws.com/api/v1/courses/uuid/".$validated['course_id'], $validated);

                if ($response->successful()) {
                    //redirect to the course  page with success message
                    $request->session()->flash('success', 'Course settings updated successfully');
                    return redirect()->route('courses.index');
                }

            } catch (\Exception $e) {
                // Log any exceptions
                Log::error('Exception during settings API request:', ['message' => $e->getMessage()]);

                // Return the course_id even on exception so flow can continue
                return redirect()->route('courses.settings', ['course_id' => $validated['course_id']]);
            }
    }

    /**
     * Get user courses for API consumption (used by Dashboard)
     */
    public function getUserCourses(Request $request)
    {
        try {
            // Get user UUID - handle both authenticated and unauthenticated requests
            $user = $request->user();
            if (!$user) {
                // For demo purposes, return empty array if no user
                return response()->json([
                    'success' => true,
                    'courses' => [],
                    'message' => 'No user authenticated'
                ]);
            }

            $user_uuid = $user->uuid;

            Log::info('Fetching courses for user', ['user_uuid' => $user_uuid]);

            // Make a GET request to the external API with improved settings
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(15) // Reduced timeout to 15 seconds
              ->connectTimeout(5) // Reduced connection timeout to 5 seconds
              ->retry(1, 500) // Single retry with 500ms delay
              ->get('http://ec2-51-20-190-50.eu-north-1.compute.amazonaws.com/api/v1/courses/'.$user_uuid);

            if ($response->successful()) {
                $courses = $response->json();

                Log::info('External API returned courses', [
                    'count' => is_array($courses) ? count($courses) : 0,
                    'user_uuid' => $user_uuid
                ]);

                // Transform courses data for Dashboard consumption
                $transformedCourses = collect($courses)->map(function ($course) {
                    // Debug log for course structure
                    Log::info('Processing course for Dashboard', [
                        'course_id' => $course['id'] ?? 'unknown',
                        'title' => $course['title'] ?? 'no title',
                        'progress' => $course['progress'] ?? 'no progress',
                        'difficulty_level' => $course['difficulty_level'] ?? 'no difficulty'
                    ]);

                    // Determine next lesson based on modules/folders
                    $nextLesson = 'Start Learning';
                    if (!empty($course['modules']) && count($course['modules']) > 0) {
                        $nextLesson = 'Continue Module';
                    } elseif (!empty($course['folders']) && count($course['folders']) > 0) {
                        $nextLesson = 'Explore Content';
                    }

                    return [
                        'id' => $course['id'] ?? null,
                        'uuid' => $course['uuid'] ?? null,
                        'title' => $course['title'] ?? 'Untitled Course',
                        'progress' => (int) ($course['progress'] ?? 0),
                        'description' => $course['description'] ?? '',
                        'difficulty' => $course['difficulty_level'] ?? 'Beginner',
                        'thumbnail' => $course['image'] ?? null,
                        'isNew' => isset($course['created_at']) ?
                            Carbon::parse($course['created_at'])->diffInDays(now()) <= 7 : false,
                        'nextLesson' => $nextLesson,
                        'timeLeft' => $this->calculateTimeLeft((int) ($course['progress'] ?? 0)),
                        'lastAccessed' => $course['updated_at'] ?? $course['created_at'] ?? now(),
                        'modules_count' => count($course['modules'] ?? []),
                        'categories' => $course['categories'] ?? [],
                    ];
                })->filter(function ($course) {
                    return $course['id'] !== null; // Only include courses with valid IDs
                })->values()->take(4); // Limit to 4 courses for Dashboard

                return response()->json([
                    'success' => true,
                    'courses' => $transformedCourses,
                    'message' => 'Courses retrieved successfully'
                ]);
            } else {
                Log::error('Failed to fetch courses for API', [
                    'status' => $response->status(),
                    'response_body' => $response->body(),
                    'user_uuid' => $user_uuid
                ]);

                // Return sample course data when API fails
                return $this->getFallbackCourseData();
            }
        } catch (\Exception $e) {
            Log::error('Exception during courses API request', [
                'error' => $e->getMessage(),
                'user_uuid' => $user_uuid,
                'trace' => $e->getTraceAsString()
            ]);

            // Check if it's a timeout or connection error
            if (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'Connection')) {
                Log::warning('External courses API is unreachable, using fallback data');
                return $this->getFallbackCourseData();
            }

            return response()->json([
                'success' => false,
                'courses' => [],
                'error' => 'An error occurred while loading courses'
            ], 500);
        }
    }

    /**
     * Calculate estimated time left based on progress
     */
    private function calculateTimeLeft($progress)
    {
        // Ensure progress is an integer to avoid type errors
        $progress = (int) $progress;

        if ($progress >= 100) {
            return 'Completed';
        }

        // Estimate based on average course length and progress
        $averageCourseDuration = 8; // hours
        $remainingPercentage = (100 - $progress) / 100;
        $hoursLeft = $averageCourseDuration * $remainingPercentage;

        if ($hoursLeft < 1) {
            return round($hoursLeft * 60) . ' minutes';
        } else if ($hoursLeft < 2) {
            return '1 hour';
        } else {
            return round($hoursLeft, 1) . ' hours';
        }
    }

    /**
     * Get user courses by UUID for API consumption (no authentication required)
     */
    public function getCoursesByUuid(Request $request, string $user_uuid)
    {
        try {
            Log::info('Fetching courses for user UUID', ['user_uuid' => $user_uuid]);

            // Validate that the UUID exists
            $user = \App\Models\User::where('uuid', $user_uuid)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The specified user UUID does not exist'
                ], 404);
            }

            // For development and testing, return fallback data to avoid external API dependency
            if (app()->environment(['local', 'testing']) || config('app.debug')) {
                Log::info('Development mode: returning fallback course data', ['user_uuid' => $user_uuid]);
                return response()->json([
                    'success' => true,
                    'courses' => $this->getFallbackCourseData(),
                    'message' => 'Using fallback data (development mode)'
                ]);
            }

            // Make a GET request to the external API with improved settings
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->timeout(15) // Reduced timeout to 15 seconds
              ->connectTimeout(5) // Reduced connection timeout to 5 seconds
              ->retry(1, 500) // Single retry with 500ms delay
              ->get('http://ec2-51-20-190-50.eu-north-1.compute.amazonaws.com/api/v1/courses/'.$user_uuid);

            if ($response->successful()) {
                $courses = $response->json();

                Log::info('External API returned courses', [
                    'count' => is_array($courses) ? count($courses) : 0,
                    'user_uuid' => $user_uuid
                ]);

                // Transform courses data for Dashboard consumption
                $transformedCourses = collect($courses)->map(function ($course) {
                    // Debug log for course structure
                    Log::info('Processing course for Dashboard', [
                        'course_id' => $course['id'] ?? 'unknown',
                        'title' => $course['title'] ?? 'no title',
                        'progress' => $course['progress'] ?? 'no progress',
                        'difficulty_level' => $course['difficulty_level'] ?? 'no difficulty'
                    ]);

                    // Determine next lesson based on modules/folders
                    $nextLesson = 'Start Learning';
                    if (!empty($course['modules']) && count($course['modules']) > 0) {
                        $nextLesson = 'Continue Module';
                    } elseif (!empty($course['folders']) && count($course['folders']) > 0) {
                        $nextLesson = 'Explore Content';
                    }

                    return [
                        'id' => $course['id'] ?? null,
                        'uuid' => $course['uuid'] ?? null,
                        'title' => $course['title'] ?? 'Untitled Course',
                        'progress' => (int) ($course['progress'] ?? 0),
                        'description' => $course['description'] ?? '',
                        'difficulty' => $course['difficulty_level'] ?? 'Beginner',
                        'thumbnail' => $course['image'] ?? null,
                        'isNew' => isset($course['created_at']) ?
                            Carbon::parse($course['created_at'])->diffInDays(now()) <= 7 : false,
                        'nextLesson' => $nextLesson,
                        'timeLeft' => $this->calculateTimeLeft($course['progress'] ?? 0),
                        'modules' => $course['modules'] ?? [],
                        'folders' => $course['folders'] ?? []
                    ];
                })->toArray();

                Log::info('Successfully transformed courses for Dashboard', [
                    'transformed_count' => count($transformedCourses),
                    'user_uuid' => $user_uuid
                ]);

                return response()->json([
                    'success' => true,
                    'courses' => $transformedCourses,
                    'message' => 'Courses retrieved successfully'
                ]);

            } else {
                Log::warning('External API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'user_uuid' => $user_uuid
                ]);

                // Return fallback data for development or when external API fails
                if (app()->environment(['local', 'testing']) || config('app.debug')) {
                    return response()->json([
                        'success' => true,
                        'courses' => $this->getFallbackCourseData(),
                        'message' => 'Using fallback data (external API unavailable)'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'error' => 'External API unavailable',
                    'message' => 'Course service is temporarily unavailable'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('Error fetching courses by UUID', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_uuid' => $user_uuid
            ]);

            // Return fallback data for development or when errors occur
            if (app()->environment(['local', 'testing']) || config('app.debug')) {
                return response()->json([
                    'success' => true,
                    'courses' => $this->getFallbackCourseData(),
                    'message' => 'Using fallback data due to error (development mode)'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to retrieve courses. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get fallback course data with proper structure for Dashboard
     */
    private function getFallbackCourseData()
    {
        Log::info('Returning fallback course data due to API unavailability');

        // Use actual course structure from your API
        $courses = [
            [
                "id" => 1,
                "uuid" => "657fbf44-02d8-446a-b517-4203571aeeb2",
                "title" => "Quibusdam et volupta",
                "description" => "Autem dolores non quisquam assumenda totam sit aliquid qui eveniet",
                "image" => "https://picsum.photos/800/450?random=596",
                "prerequisite" => null,
                "start_date" => null,
                "end_date" => null,
                "difficulty_level" => null,
                "progress" => 0,
                "completion_message" => null,
                "created_at" => "2025-08-07T21:43:57.000000Z",
                "updated_at" => "2025-08-07T21:43:57.000000Z",
                "user_uuids" => [
                    [
                        "id" => 1,
                        "course_id" => 1,
                        "user_uuid" => "4d7c7b14-0344-4029-a489-28d5d24bc030",
                        "created_at" => "2025-08-07T21:43:57.000000Z",
                        "updated_at" => "2025-08-07T21:43:57.000000Z"
                    ]
                ],
                "categories" => [
                    [
                        "id" => 34,
                        "title" => "Virtual Reality (VR)",
                        "created_at" => "2025-08-07T21:17:53.000000Z",
                        "updated_at" => "2025-08-07T21:17:53.000000Z",
                        "icon" => null,
                        "pivot" => [
                            "course_id" => 1,
                            "category_id" => 34
                        ]
                    ]
                ],
                "modules" => [],
                "folders" => [
                    [
                        "id" => 1,
                        "name" => "Quibusdam et volupta Folder",
                        "course_id" => 1,
                        "created_at" => "2025-08-07T21:43:57.000000Z",
                        "updated_at" => "2025-08-07T21:43:57.000000Z"
                    ]
                ]
            ]
        ];

        // Transform courses data for Dashboard consumption (same as main API logic)
        $transformedCourses = collect($courses)->map(function ($course) {
            // Debug log for course structure
            Log::info('Processing fallback course for Dashboard', [
                'course_id' => $course['id'] ?? 'unknown',
                'title' => $course['title'] ?? 'no title',
                'progress' => $course['progress'] ?? 'no progress',
                'difficulty_level' => $course['difficulty_level'] ?? 'no difficulty'
            ]);

            // Determine next lesson based on modules/folders
            $nextLesson = 'Start Learning';
            if (!empty($course['modules']) && count($course['modules']) > 0) {
                $nextLesson = 'Continue Module';
            } elseif (!empty($course['folders']) && count($course['folders']) > 0) {
                $nextLesson = 'Explore Content';
            }

            return [
                'id' => $course['id'] ?? null,
                'uuid' => $course['uuid'] ?? null,
                'title' => $course['title'] ?? 'Untitled Course',
                'progress' => (int) ($course['progress'] ?? 0),
                'description' => $course['description'] ?? '',
                'difficulty' => $course['difficulty_level'] ?? 'Beginner',
                'thumbnail' => $course['image'] ?? null,
                'isNew' => isset($course['created_at']) ?
                    Carbon::parse($course['created_at'])->diffInDays(now()) <= 7 : false,
                'nextLesson' => $nextLesson,
                'timeLeft' => $this->calculateTimeLeft((int) ($course['progress'] ?? 0)),
                'lastAccessed' => $course['updated_at'] ?? $course['created_at'] ?? now(),
                'modules_count' => count($course['modules'] ?? []),
                'categories' => $course['categories'] ?? [],
            ];
        })->filter(function ($course) {
            return $course['id'] !== null; // Only include courses with valid IDs
        })->values()->take(4); // Limit to 4 courses for Dashboard

        return $transformedCourses->toArray();
    }
}
