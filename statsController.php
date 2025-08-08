<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FocusSession;
use App\Models\Stats;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Stats Controller
 *
 * Handles user statistics and metrics related to focus sessions and productivity.
 * This controller manages endpoints for retrieving aggregated data about user's
 * study time, efficiency, streaks, and other performance metrics.
 *
 * @package App\Http\Controllers\Api\V1
 * @author FocusFlow Team
 * @version 1.0.0
 */
class StatsController extends Controller
{
    /**
     * Get the total actual time spent by a user across all focus sessions.
     *
     * This method calculates the sum of all actual_time values from the user's
     * focus sessions, converts from HH:MM:SS format to seconds for calculation,
     * then converts back to HH:MM:SS format for the response.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @param int|null $user_id Optional user ID parameter. If not provided, uses authenticated user
     * @return \Illuminate\Http\JsonResponse JSON response containing total study time
     *
     * @throws \Exception When user is not authenticated or database errors occur
     *
     * @api
     * @method GET
     * @route /api/v1/stats/actual-time/{user_id?}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Total actual time retrieved successfully",
     *   "stats": {
     *     "total_time": "02:45:30"
     *   }
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "error": "Authentication required",
     *   "message": "Please login to access this resource"
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "error": "User not found",
     *   "message": "The specified user does not exist"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "error": "Internal server error",
     *   "message": "Failed to calculate total actual time"
     * }
     */
    public function getActualTime(Request $request, $user_id = null): JsonResponse
    {
        Log::info('Stats API: getActualTime called', [
            'user_id_param' => $user_id,
            'authenticated_user' => Auth::id(),
            'request_ip' => $request->ip()
        ]);

        try {
            // Get the target user
            $targetUser = $this->resolveTargetUser($user_id);

            if (!$targetUser['success']) {
                return response()->json($targetUser['response'], $targetUser['status']);
            }

            $userId = $targetUser['user_id'];

            // Check if user exists
            $user = User::find($userId);
            if (!$user) {
                Log::warning('Stats API: User not found', ['user_id' => $userId]);
                return response()->json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The specified user does not exist'
                ], 404);
            }

            // Get all focus sessions for the user
            $sessions = FocusSession::where('user_id', $userId)->get();

            Log::info('Stats API: Calculating total actual time', [
                'user_id' => $userId,
                'total_sessions' => $sessions->count()
            ]);

            // Handle case where user has no sessions
            if ($sessions->isEmpty()) {
                Log::info('Stats API: No sessions found for user', ['user_id' => $userId]);
                return response()->json([
                    'success' => true,
                    'message' => 'No focus sessions found for user',
                    'stats' => [
                        'total_time' => '00:00:00',
                        'session_count' => 0
                    ],
                    'info' => 'User has not completed any focus sessions yet'
                ]);
            }

            $totalSeconds = 0;

            // Sum up all actual_time values
            foreach ($sessions as $session) {
                if ($session->actual_time) {
                    $totalSeconds += $this->timeToSeconds($session->actual_time);
                }
            }

            // Convert total seconds back to HH:MM:SS format
            $totalTime = $this->secondsToTime($totalSeconds);

            Log::info('Stats API: Total actual time calculated successfully', [
                'user_id' => $userId,
                'total_seconds' => $totalSeconds,
                'total_time' => $totalTime,
                'session_count' => $sessions->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Total actual time retrieved successfully',
                'stats' => [
                    'total_time' => $totalTime,
                    'session_count' => $sessions->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stats API: Failed to calculate total actual time', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user_id ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to calculate total actual time. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get comprehensive stats for a user.
     *
     * This method returns all available statistics for a user including
     * total study time, average efficiency, current streak, etc.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @param int|null $user_id Optional user ID parameter
     * @return \Illuminate\Http\JsonResponse JSON response containing all user stats
     *
     * @api
     * @method GET
     * @route /api/v1/stats/{user_id?}
     *
     * @response 200 {
     *   "success": true,
     *   "message": "User stats retrieved successfully",
     *   "stats": {
     *     "total_time": "02:45:30",
     *     "average_efficiency": 85.5,
     *     "current_streak": 7,
     *     "longest_streak": 15,
     *     "total_sessions": 25,
     *     "completed_sessions": 22
     *   }
     * }
     */
    public function getStats(Request $request, $user_id = null): JsonResponse
    {
        Log::info('Stats API: getStats called', [
            'user_id_param' => $user_id,
            'authenticated_user' => Auth::guard('web')->id(),
            'auth_user_object' => Auth::guard('web')->user() ? Auth::guard('web')->user()->only(['id', 'uuid', 'name']) : null,
            'request_ip' => $request->ip()
        ]);

        try {
            // Get the target user
            $targetUser = $this->resolveTargetUser($user_id);

            if (!$targetUser['success']) {
                return response()->json($targetUser['response'], $targetUser['status']);
            }

            $userId = $targetUser['user_id'];

            // Check if user exists
            $user = User::find($userId);
            if (!$user) {
                Log::warning('Stats API: User not found', ['user_id' => $userId]);
                return response()->json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The specified user does not exist'
                ], 404);
            }

            Log::info('Stats API: Resolved user details', [
                'resolved_user_id' => $userId,
                'user_name' => $user->name,
                'user_uuid' => $user->uuid
            ]);

            // Check if user has any focus sessions
            $sessionCount = FocusSession::where('user_id', $userId)->count();
            $activeSessions = FocusSession::where('user_id', $userId)->where('status', 'active')->count();
            $completedSessions = FocusSession::where('user_id', $userId)->where('status', 'completed')->count();
            $sessionsWithDuration = FocusSession::where('user_id', $userId)->where('duration', '>', 0)->count();

            Log::info('Stats API: Session counts for user', [
                'user_id' => $userId,
                'total_sessions' => $sessionCount,
                'active_sessions' => $activeSessions,
                'completed_sessions' => $completedSessions,
                'sessions_with_duration' => $sessionsWithDuration
            ]);

            if ($sessionCount === 0) {
                Log::info('Stats API: No sessions found for user', ['user_id' => $userId]);
                return response()->json([
                    'success' => true,
                    'message' => 'No focus sessions found for user',
                    'stats' => [
                        'total_time' => '00:00:00',
                        'best_focus_time' => 'No data',
                        'most_productive_day' => 'No data',
                        'average_efficiency' => 0.00,
                        'weekly_insight' => [
                            'monday' => '00:00:00',
                            'tuesday' => '00:00:00',
                            'wednesday' => '00:00:00',
                            'thursday' => '00:00:00',
                            'friday' => '00:00:00',
                            'saturday' => '00:00:00',
                            'sunday' => '00:00:00'
                        ],
                        'achievements' => [],
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'total_sessions' => 0,
                        'completed_sessions' => 0,
                        'last_session_date' => null
                    ],
                    'info' => 'User has not completed any focus sessions yet. Start your first session to see statistics!'
                ]);
            }

            // Update stats for the user
            Stats::updateStatsForUser($userId);

            // Get the updated stats
            $stats = Stats::forUser($userId)->first();

            if (!$stats) {
                Log::warning('Stats API: Stats record not found after update', ['user_id' => $userId]);

                // Create default stats response if record doesn't exist
                return response()->json([
                    'success' => true,
                    'message' => 'User stats initialized',
                    'stats' => [
                        'total_time' => '00:00:00',
                        'best_focus_time' => 'No data',
                        'most_productive_day' => 'No data',
                        'average_efficiency' => 0,
                        'weekly_insight' => [
                            'monday' => '00:00:00',
                            'tuesday' => '00:00:00',
                            'wednesday' => '00:00:00',
                            'thursday' => '00:00:00',
                            'friday' => '00:00:00',
                            'saturday' => '00:00:00',
                            'sunday' => '00:00:00'
                        ],
                        'achievements' => [],
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'total_sessions' => 0,
                        'completed_sessions' => 0,
                        'last_session_date' => null
                    ],
                    'info' => 'Welcome to FocusFlow! Start your first session to see statistics.'
                ]);
            }

            Log::info('Stats API: User stats retrieved successfully', [
                'user_id' => $userId,
                'total_sessions' => $stats->total_sessions,
                'total_study_time' => $stats->total_study_time,
                'completed_sessions' => $stats->completed_sessions,
                'current_streak' => $stats->current_streak,
                'average_efficiency' => $stats->average_efficiency
            ]);

            // Calculate best focus time
            $bestFocusTime = $this->calculateBestFocusTime($userId);

            // Calculate weekly insights
            $weeklyInsight = $this->calculateWeeklyInsight($userId);

            // Calculate achievements
            $achievements = $this->calculateAchievements($userId);

            // Calculate most productive day (use a simple method for now)
            $mostProductiveDay = 'Monday'; // Default value, can be enhanced later

            return response()->json([
                'success' => true,
                'message' => 'User stats retrieved successfully',
                'stats' => [
                    'total_time' => $stats->total_study_time,
                    'best_focus_time' => $bestFocusTime,
                    'most_productive_day' => $mostProductiveDay,
                    'average_efficiency' => $stats->average_efficiency,
                    'weekly_insight' => $weeklyInsight,
                    'achievements' => $achievements,
                    'current_streak' => $stats->current_streak,
                    'longest_streak' => $stats->longest_streak,
                    'total_sessions' => $stats->total_sessions,
                    'completed_sessions' => $stats->completed_sessions,
                    'last_session_date' => $stats->last_session_date
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stats API: Failed to get user stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user_id ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to retrieve user stats. Please try again later.'
            ], 500);
        }
    }

    /**
     * Health check endpoint for stats API.
     *
     * This endpoint provides basic information about the stats system
     * without requiring authentication. Useful for monitoring and testing.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @return \Illuminate\Http\JsonResponse JSON response with system status
     *
     * @api
     * @method GET
     * @route /api/v1/stats/health
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Stats API is operational",
     *   "system": {
     *     "status": "healthy",
     *     "timestamp": "2025-08-06T15:30:00Z",
     *     "total_users": 5,
     *     "total_sessions": 318,
     *     "database_connection": "ok"
     *   }
     * }
     */
    public function health(Request $request): JsonResponse
    {
        try {
            Log::info('Stats API: Health check requested', [
                'request_ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Test database connection
            $databaseStatus = 'ok';
            $totalUsers = 0;
            $totalSessions = 0;

            try {
                $totalUsers = User::count();
                $totalSessions = FocusSession::count();
            } catch (\Exception $e) {
                $databaseStatus = 'error';
                Log::warning('Stats API: Database connection issue during health check', [
                    'error' => $e->getMessage()
                ]);
            }

            // Check if stats table exists
            $statsTableExists = false;
            try {
                \Illuminate\Support\Facades\DB::table('stats')->count();
                $statsTableExists = true;
            } catch (\Exception $e) {
                Log::warning('Stats API: Stats table check failed', [
                    'error' => $e->getMessage()
                ]);
            }

            $status = ($databaseStatus === 'ok' && $statsTableExists) ? 'healthy' : 'degraded';

            return response()->json([
                'success' => true,
                'message' => 'Stats API is operational',
                'system' => [
                    'status' => $status,
                    'timestamp' => now()->toISOString(),
                    'total_users' => $totalUsers,
                    'total_sessions' => $totalSessions,
                    'database_connection' => $databaseStatus,
                    'stats_table_exists' => $statsTableExists,
                    'version' => '1.0.0'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stats API: Health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Stats API health check failed',
                'system' => [
                    'status' => 'error',
                    'timestamp' => now()->toISOString(),
                    'error' => 'Health check failed'
                ]
            ], 500);
        }
    }

    /**
     * Get public stats summary without authentication.
     *
     * This endpoint provides general statistics about the platform
     * without exposing user-specific data. Useful for public dashboards.
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @return \Illuminate\Http\JsonResponse JSON response with public stats
     *
     * @api
     * @method GET
     * @route /api/v1/stats/public
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Public stats retrieved successfully",
     *   "stats": {
     *     "total_users": 150,
     *     "total_sessions": 1250,
     *     "total_study_time": "450:30:15",
     *     "average_session_time": "00:25:30"
     *   }
     * }
     */
    public function publicStats(Request $request): JsonResponse
    {
        try {
            Log::info('Stats API: Public stats requested', [
                'request_ip' => $request->ip()
            ]);

            // Get aggregate statistics
            $totalUsers = User::count();
            $totalSessions = FocusSession::count();
            $completedSessions = FocusSession::where('status', 'completed')->count();

            // Calculate total study time across all users
            $allSessions = FocusSession::whereNotNull('actual_time')->get();
            $totalSeconds = 0;

            foreach ($allSessions as $session) {
                if ($session->actual_time) {
                    $totalSeconds += $this->timeToSeconds($session->actual_time);
                }
            }

            $totalStudyTime = $this->secondsToTime($totalSeconds);

            // Calculate average session time
            $averageSeconds = $totalSessions > 0 ? intval($totalSeconds / $totalSessions) : 0;
            $averageSessionTime = $this->secondsToTime($averageSeconds);

            return response()->json([
                'success' => true,
                'message' => 'Public stats retrieved successfully',
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_sessions' => $totalSessions,
                    'completed_sessions' => $completedSessions,
                    'total_study_time' => $totalStudyTime,
                    'average_session_time' => $averageSessionTime,
                    'completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0
                ],
                'note' => 'These are aggregate statistics and do not include personal user data'
            ]);

        } catch (\Exception $e) {
            Log::error('Stats API: Failed to get public stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to retrieve public stats. Please try again later.'
            ], 500);
        }
    }

    /**
     * Resolve the target user for stats operations.
     *
     * @param int|null $user_id The user ID parameter
     * @return array Result array with success status and user info
     */
    private function resolveTargetUser($user_id): array
    {
        // If user_id is not provided, try to use authenticated user or fallback to latest user
        if ($user_id === null) {
            // Use web guard specifically for web session authentication
            $user = Auth::guard('web')->user();

            Log::info('Stats API: Resolving target user', [
                'provided_user_id' => $user_id,
                'authenticated_user' => $user ? $user->id : null,
                'authenticated_user_name' => $user ? $user->name : null,
                'auth_guard' => 'web',
                'session_has_user' => session()->has('login_web_' . sha1('web'))
            ]);

            if ($user) {
                // Check if authenticated user has focus sessions
                $sessionCount = FocusSession::where('user_id', $user->id)->count();

                // Always use authenticated user's own data
                Log::info('Stats API: Using authenticated user', [
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ]);
                return [
                    'success' => true,
                    'user_id' => $user->id
                ];
            } else {
                // No authenticated user - return error
                Log::warning('Stats API: No authenticated user found');
                return [
                    'success' => false,
                    'status' => 401,
                    'response' => [
                        'success' => false,
                        'error' => 'Authentication required',
                        'message' => 'Please log in to view your statistics.'
                    ]
                ];
            }
        } else {
            // User ID is provided, use it directly
            Log::info('Stats API: Using provided user ID', ['user_id' => $user_id]);
            return [
                'success' => true,
                'user_id' => $user_id
            ];
        }
    }

    /**
     * Convert time string in HH:MM:SS format to total seconds.
     *
     * @param string $time Time in HH:MM:SS format
     * @return int Total seconds
     *
     * @example
     * $seconds = $this->timeToSeconds('01:30:45'); // Returns 5445
     */
    private function timeToSeconds(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    /**
     * Convert total seconds to time string in HH:MM:SS format.
     *
     * @param int $seconds Total seconds
     * @return string Time in HH:MM:SS format
     *
     * @example
     * $time = $this->secondsToTime(5445); // Returns '01:30:45'
     */
    private function secondsToTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }

    /**
     * Calculate the best focus time for a user based on their session start times.
     *
     * This method analyzes all focus sessions for a user and determines the hour
     * of the day when they have the most focus sessions, indicating their peak
     * productivity time.
     *
     * @param int $userId The user ID to calculate best focus time for
     * @return string Best focus time in 12-hour format (e.g., "11:00pm")
     */
    private function calculateBestFocusTime(int $userId): string
    {
        // Get all focus sessions for the user from the past week to get recent patterns
        $oneWeekAgo = now()->subWeek();
        $sessions = FocusSession::where('user_id', $userId)
            ->where('started_at', '>=', $oneWeekAgo)
            ->whereNotNull('started_at')
            ->get();

        // If no recent sessions, fall back to all sessions
        if ($sessions->isEmpty()) {
            $sessions = FocusSession::where('user_id', $userId)
                ->whereNotNull('started_at')
                ->get();
        }

        // If still no sessions, return default
        if ($sessions->isEmpty()) {
            return 'No data';
        }

        // Group sessions by hour of the day
        $hourCounts = [];

        foreach ($sessions as $session) {
            $hour = $session->started_at->format('H'); // 24-hour format (00-23)
            $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
        }

        // Find the hour with the most sessions
        $bestHour = array_keys($hourCounts, max($hourCounts))[0];

        // Convert 24-hour format to 12-hour format with am/pm
        $hour24 = (int) $bestHour;
        $period = $hour24 >= 12 ? 'pm' : 'am';
        $hour12 = $hour24 > 12 ? $hour24 - 12 : ($hour24 == 0 ? 12 : $hour24);

        return sprintf('%d:00%s', $hour12, $period);
    }

    /**
     * Calculate weekly insights for a user showing focus time for each day of the week.
     *
     * This method analyzes all focus sessions from the current week and calculates
     * the total focus time (actual_time) for each day of the week.
     *
     * @param int $userId The user ID to calculate weekly insights for
     * @return array Weekly insights with focus time for each day
     */
    private function calculateWeeklyInsight(int $userId): array
    {
        // Get the start and end of the current week (Monday to Sunday)
        $startOfWeek = now()->startOfWeek(); // Monday
        $endOfWeek = now()->endOfWeek(); // Sunday

        // Get all focus sessions for the current week (include sessions with duration or actual_time)
        $sessions = FocusSession::where('user_id', $userId)
            ->whereBetween('started_at', [$startOfWeek, $endOfWeek])
            ->where(function($query) {
                $query->whereNotNull('actual_time')
                      ->orWhere('duration', '>', 0);
            })
            ->get();

        // Initialize weekly insights with zero time for each day
        $weeklyInsights = [
            'monday' => '00:00:00',
            'tuesday' => '00:00:00',
            'wednesday' => '00:00:00',
            'thursday' => '00:00:00',
            'friday' => '00:00:00',
            'saturday' => '00:00:00',
            'sunday' => '00:00:00'
        ];

        // Group sessions by day of week and calculate total time for each day
        $dailySeconds = [
            'monday' => 0,
            'tuesday' => 0,
            'wednesday' => 0,
            'thursday' => 0,
            'friday' => 0,
            'saturday' => 0,
            'sunday' => 0
        ];

        foreach ($sessions as $session) {
            $dayOfWeek = strtolower($session->started_at->format('l')); // 'monday', 'tuesday', etc.

            if (isset($dailySeconds[$dayOfWeek])) {
                // Use actual_time if it has meaningful data (not 00:00:00), otherwise use duration
                if ($session->actual_time && $session->actual_time !== '00:00:00') {
                    $dailySeconds[$dayOfWeek] += $this->timeToSeconds($session->actual_time);
                } elseif ($session->duration > 0) {
                    // Duration is already in seconds
                    $dailySeconds[$dayOfWeek] += $session->duration;
                }
            }
        }

        // Convert seconds back to HH:MM:SS format for each day
        foreach ($dailySeconds as $day => $seconds) {
            $weeklyInsights[$day] = $this->secondsToTime($seconds);
        }

        return $weeklyInsights;
    }

    /**
     * Calculate achievements for a user based on their focus sessions and activities.
     *
     * This method analyzes user's focus sessions, tasks, and other activities to determine
     * which achievements they have unlocked and their progress towards other achievements.
     *
     * @param int $userId The user ID to calculate achievements for
     * @return array Array of achievement objects with progress and status
     */
    private function calculateAchievements(int $userId): array
    {
        // Get user's focus sessions and tasks data
        $totalSessions = FocusSession::where('user_id', $userId)->count();
        $completedSessions = FocusSession::where('user_id', $userId)->where('status', 'completed')->count();

        // Estimate completed tasks based on completed sessions
        $completedTasks = $completedSessions * 2; // Assume 2 tasks per session average

        // Get early bird sessions (before 9am) - simplified
        $earlyBirdSessions = 0;
        try {
            $earlyBirdSessions = FocusSession::where('user_id', $userId)
                ->whereRaw('HOUR(started_at) < 9')
                ->where('status', 'completed')
                ->count();
        } catch (\Exception $e) {
            // Fallback: estimate based on total sessions
            $earlyBirdSessions = intval($completedSessions * 0.2);
        }

        // Get night owl sessions (after 9pm) - simplified
        $nightOwlSessions = 0;
        try {
            $nightOwlSessions = FocusSession::where('user_id', $userId)
                ->whereRaw('HOUR(started_at) >= 21')
                ->where('status', 'completed')
                ->count();
        } catch (\Exception $e) {
            // Fallback: estimate based on total sessions
            $nightOwlSessions = intval($completedSessions * 0.15);
        }

        // Note: Course completion would need a courses table - using placeholder for now
        $completedCourses = 0; // TODO: Implement when courses table exists

        // Define achievements with their criteria
        $achievements = [
            [
                'id' => 1,
                'name' => 'Focus Master',
                'description' => 'Complete 10 focus sessions',
                'icon' => 'Award',
                'target' => 10,
                'current' => $completedSessions,
                'achieved' => $completedSessions >= 10,
                'progress' => min(100, round(($completedSessions / 10) * 100)),
                'level' => $this->calculateAchievementLevel($completedSessions, 10)
            ],
            [
                'id' => 2,
                'name' => 'Task Champion',
                'description' => 'Complete 50 tasks',
                'icon' => 'CheckCircle',
                'target' => 50,
                'current' => $completedTasks,
                'achieved' => $completedTasks >= 50,
                'progress' => min(100, round(($completedTasks / 50) * 100)),
                'level' => $this->calculateAchievementLevel($completedTasks, 50)
            ],
            [
                'id' => 3,
                'name' => 'Early Bird',
                'description' => '5 focus sessions before 9am',
                'icon' => 'Zap',
                'target' => 5,
                'current' => $earlyBirdSessions,
                'achieved' => $earlyBirdSessions >= 5,
                'progress' => min(100, round(($earlyBirdSessions / 5) * 100)),
                'level' => $this->calculateAchievementLevel($earlyBirdSessions, 5)
            ],
            [
                'id' => 4,
                'name' => 'Night Owl',
                'description' => 'Complete 5 sessions after 9pm',
                'icon' => 'Brain',
                'target' => 5,
                'current' => $nightOwlSessions,
                'achieved' => $nightOwlSessions >= 5,
                'progress' => min(100, round(($nightOwlSessions / 5) * 100)),
                'level' => $this->calculateAchievementLevel($nightOwlSessions, 5)
            ],
            [
                'id' => 5,
                'name' => 'Productivity Guru',
                'description' => 'Complete 100 tasks',
                'icon' => 'Activity',
                'target' => 100,
                'current' => $completedTasks,
                'achieved' => $completedTasks >= 100,
                'progress' => min(100, round(($completedTasks / 100) * 100)),
                'level' => $this->calculateAchievementLevel($completedTasks, 100)
            ],
            [
                'id' => 6,
                'name' => 'Knowledge Seeker',
                'description' => 'Complete 3 courses',
                'icon' => 'BookOpen',
                'target' => 3,
                'current' => $completedCourses,
                'achieved' => $completedCourses >= 3,
                'progress' => min(100, round(($completedCourses / 3) * 100)),
                'level' => $this->calculateAchievementLevel($completedCourses, 3)
            ]
        ];

        Log::info('Achievements calculated for user', [
            'user_id' => $userId,
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'completed_tasks' => $completedTasks,
            'early_bird_sessions' => $earlyBirdSessions,
            'night_owl_sessions' => $nightOwlSessions,
            'achieved_count' => count(array_filter($achievements, fn($a) => $a['achieved']))
        ]);

        return $achievements;
    }

    /**
     * Calculate achievement level based on progress beyond the basic requirement.
     *
     * @param int $current Current progress value
     * @param int $target Target value for the achievement
     * @return int Achievement level (0-3)
     */
    private function calculateAchievementLevel(int $current, int $target): int
    {
        if ($current < $target) {
            return 0; // Not achieved yet
        }

        // Calculate level based on how much they've exceeded the target
        $ratio = $current / $target;

        if ($ratio >= 5) {
            return 3; // Master level
        } elseif ($ratio >= 3) {
            return 2; // Expert level
        } elseif ($ratio >= 1.5) {
            return 1; // Advanced level
        } else {
            return 1; // Basic achievement level
        }
    }

    /**
     * Get user statistics by UUID (no authentication required)
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @param string $user_uuid The user UUID
     * @return \Illuminate\Http\JsonResponse JSON response containing user stats
     *
     * @api
     * @method GET
     * @route /api/v1/stats/uuid/{user_uuid}
     */
    public function getStatsByUuid(Request $request, string $user_uuid): JsonResponse
    {
        Log::info('Stats API (UUID): getStatsByUuid called', [
            'user_uuid' => $user_uuid,
            'request_ip' => $request->ip()
        ]);

        try {
            // Find user by UUID
            $user = User::where('uuid', $user_uuid)->first();

            if (!$user) {
                Log::warning('Stats API (UUID): User not found', ['user_uuid' => $user_uuid]);
                return response()->json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The specified user UUID does not exist'
                ], 404);
            }

            $userId = $user->id;

            Log::info('Stats API (UUID): Resolved user details', [
                'resolved_user_id' => $userId,
                'user_name' => $user->name,
                'user_uuid' => $user->uuid
            ]);

            // Check if user has any focus sessions
            $sessionCount = FocusSession::where('user_id', $userId)->count();
            $activeSessions = FocusSession::where('user_id', $userId)->where('status', 'active')->count();
            $completedSessions = FocusSession::where('user_id', $userId)->where('status', 'completed')->count();
            $sessionsWithDuration = FocusSession::where('user_id', $userId)->where('duration', '>', 0)->count();

            Log::info('Stats API (UUID): Session counts for user', [
                'user_id' => $userId,
                'total_sessions' => $sessionCount,
                'active_sessions' => $activeSessions,
                'completed_sessions' => $completedSessions,
                'sessions_with_duration' => $sessionsWithDuration
            ]);

            if ($sessionCount === 0) {
                Log::info('Stats API (UUID): No sessions found for user', ['user_id' => $userId]);
                return response()->json([
                    'success' => true,
                    'message' => 'No focus sessions found for user',
                    'stats' => [
                        'total_time' => '00:00:00',
                        'best_focus_time' => 'No data',
                        'most_productive_day' => 'No data',
                        'average_efficiency' => 0.00,
                        'weekly_insight' => [
                            'monday' => '00:00:00',
                            'tuesday' => '00:00:00',
                            'wednesday' => '00:00:00',
                            'thursday' => '00:00:00',
                            'friday' => '00:00:00',
                            'saturday' => '00:00:00',
                            'sunday' => '00:00:00'
                        ],
                        'achievements' => [],
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'total_sessions' => 0,
                        'completed_sessions' => 0,
                        'last_session_date' => null
                    ],
                    'info' => 'User has not completed any focus sessions yet. Start your first session to see statistics!'
                ]);
            }

            // Update stats for the user
            Stats::updateStatsForUser($userId);

            // Get the updated stats
            $stats = Stats::forUser($userId)->first();

            if (!$stats) {
                Log::warning('Stats API (UUID): Stats record not found after update', ['user_id' => $userId]);

                // Create default stats response if record doesn't exist
                return response()->json([
                    'success' => true,
                    'message' => 'User stats initialized',
                    'stats' => [
                        'total_time' => '00:00:00',
                        'best_focus_time' => 'No data',
                        'most_productive_day' => 'No data',
                        'average_efficiency' => 0,
                        'weekly_insight' => [
                            'monday' => '00:00:00',
                            'tuesday' => '00:00:00',
                            'wednesday' => '00:00:00',
                            'thursday' => '00:00:00',
                            'friday' => '00:00:00',
                            'saturday' => '00:00:00',
                            'sunday' => '00:00:00'
                        ],
                        'achievements' => [],
                        'current_streak' => 0,
                        'longest_streak' => 0,
                        'total_sessions' => 0,
                        'completed_sessions' => 0,
                        'last_session_date' => null
                    ],
                    'info' => 'Welcome to FocusFlow! Start your first session to see statistics.'
                ]);
            }

            Log::info('Stats API (UUID): User stats retrieved successfully', [
                'user_id' => $userId,
                'total_sessions' => $stats->total_sessions,
                'total_study_time' => $stats->total_study_time,
                'completed_sessions' => $stats->completed_sessions,
                'current_streak' => $stats->current_streak,
                'average_efficiency' => $stats->average_efficiency
            ]);

            // Calculate best focus time
            $bestFocusTime = $this->calculateBestFocusTime($userId);

            // Calculate weekly insights
            $weeklyInsight = $this->calculateWeeklyInsight($userId);

            // Calculate achievements
            $achievements = $this->calculateAchievements($userId);

            // Calculate most productive day (use a simple method for now)
            $mostProductiveDay = 'Monday'; // Default value, can be enhanced later

            return response()->json([
                'success' => true,
                'message' => 'User stats retrieved successfully',
                'stats' => [
                    'total_time' => $stats->total_study_time,
                    'best_focus_time' => $bestFocusTime,
                    'most_productive_day' => $mostProductiveDay,
                    'average_efficiency' => $stats->average_efficiency,
                    'weekly_insight' => $weeklyInsight,
                    'achievements' => $achievements,
                    'current_streak' => $stats->current_streak,
                    'longest_streak' => $stats->longest_streak,
                    'total_sessions' => $stats->total_sessions,
                    'completed_sessions' => $stats->completed_sessions,
                    'last_session_date' => $stats->last_session_date
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stats API (UUID): Failed to get user stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_uuid' => $user_uuid,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to retrieve user stats. Please try again later.'
            ], 500);
        }
    }

    /**
     * Get actual time by UUID (no authentication required)
     *
     * @param \Illuminate\Http\Request $request The HTTP request object
     * @param string $user_uuid The user UUID
     * @return \Illuminate\Http\JsonResponse JSON response containing actual time
     *
     * @api
     * @method GET
     * @route /api/v1/stats/actual-time/uuid/{user_uuid}
     */
    public function getActualTimeByUuid(Request $request, string $user_uuid): JsonResponse
    {
        Log::info('Stats API (UUID): getActualTimeByUuid called', [
            'user_uuid' => $user_uuid,
            'request_ip' => $request->ip()
        ]);

        try {
            // Find user by UUID
            $user = User::where('uuid', $user_uuid)->first();

            if (!$user) {
                Log::warning('Stats API (UUID): User not found for actual time', ['user_uuid' => $user_uuid]);
                return response()->json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The specified user UUID does not exist'
                ], 404);
            }

            $userId = $user->id;

            // Get all focus sessions for the user
            $sessions = FocusSession::where('user_id', $userId)->get();

            if ($sessions->isEmpty()) {
                Log::info('Stats API (UUID): No sessions found for actual time calculation', ['user_id' => $userId]);
                return response()->json([
                    'success' => true,
                    'message' => 'No focus sessions found for user',
                    'stats' => [
                        'total_time' => '00:00:00'
                    ]
                ]);
            }

            // Calculate total actual time in seconds
            $totalSeconds = 0;

            foreach ($sessions as $session) {
                if ($session->actual_time) {
                    // Convert HH:MM:SS to seconds
                    $timeParts = explode(':', $session->actual_time);
                    if (count($timeParts) === 3) {
                        $hours = (int)$timeParts[0];
                        $minutes = (int)$timeParts[1];
                        $seconds = (int)$timeParts[2];
                        $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                    }
                }
            }

            // Convert back to HH:MM:SS format
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;

            $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

            Log::info('Stats API (UUID): Actual time calculated successfully', [
                'user_id' => $userId,
                'total_seconds' => $totalSeconds,
                'formatted_time' => $formattedTime,
                'session_count' => $sessions->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Total actual time retrieved successfully',
                'stats' => [
                    'total_time' => $formattedTime
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Stats API (UUID): Failed to get actual time', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_uuid' => $user_uuid,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to retrieve actual time. Please try again later.'
            ], 500);
        }
    }
}
