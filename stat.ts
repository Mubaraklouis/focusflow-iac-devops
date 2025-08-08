import axios from 'axios';

/**
 * Interface defining weekly insights showing focus time per day
 */
export interface WeeklyInsight {
    /** Focus time on Monday in HH:MM:SS format */
    monday: string;
    /** Focus time on Tuesday in HH:MM:SS format */
    tuesday: string;
    /** Focus time on Wednesday in HH:MM:SS format */
    wednesday: string;
    /** Focus time on Thursday in HH:MM:SS format */
    thursday: string;
    /** Focus time on Friday in HH:MM:SS format */
    friday: string;
    /** Focus time on Saturday in HH:MM:SS format */
    saturday: string;
    /** Focus time on Sunday in HH:MM:SS format */
    sunday: string;
}

/**
 * Interface defining an achievement/badge structure
 */
export interface Achievement {
    /** Unique achievement ID */
    id: number;
    /** Achievement name */
    name: string;
    /** Achievement description */
    description: string;
    /** Icon name for the achievement */
    icon: string;
    /** Target value to achieve this badge */
    target: number;
    /** Current progress towards the target */
    current: number;
    /** Whether the achievement has been unlocked */
    achieved: boolean;
    /** Progress percentage (0-100) */
    progress: number;
    /** Achievement level (0-3) */
    level: number;
}

/**
 * Interface defining the structure of user statistics from the API
 */
export interface UserStats {
    /** Total time spent in focus mode (HH:MM:SS format) */
    total_time: string;
    /** Best focus time of day */
    best_focus_time: string;
    /** Most productive day based on tasks completed */
    most_productive_day: string;
    /** Weekly insights showing focus time for each day of the week */
    weekly_insight: WeeklyInsight;
    /** User achievements and badges */
    achievements: Achievement[];
    /** Average efficiency percentage */
    average_efficiency: number;
    /** Current streak in days */
    current_streak: number;
    /** Longest streak achieved */
    longest_streak: number;
    /** Total number of sessions */
    total_sessions: number;
    /** Number of completed sessions */
    completed_sessions: number;
    /** Date of last session */
    last_session_date: string | null;
}

/**
 * Interface for API response structure
 */
export interface StatsApiResponse {
    success: boolean;
    stats: UserStats;
    message?: string;
}

/**
 * Service class for handling stats-related API requests
 */
export class StatsApiService {
    private baseUrl: string;

    constructor() {
        // Use the app's base URL - adjust this based on your app's configuration
        this.baseUrl = '/api';
    }

    /**
     * Get the current user's UUID from available sources
     */
    private getUserUuid(): string | null {
        try {
            // Try to get from window object (set by Inertia)
            const pageProps = (window as any).pageProps;
            if (pageProps?.auth?.user?.uuid) {
                console.log('📡 Stats API: Found user UUID from window.pageProps:', pageProps.auth.user.uuid);
                return pageProps.auth.user.uuid;
            }

            // Try to get from global Inertia data
            const inertiaData = (window as any).Inertia;
            if (inertiaData?.page?.props?.auth?.user?.uuid) {
                console.log('📡 Stats API: Found user UUID from Inertia global:', inertiaData.page.props.auth.user.uuid);
                return inertiaData.page.props.auth.user.uuid;
            }

            // For development, use a hardcoded UUID (remove in production)
            if (import.meta.env.DEV) {
                console.warn('🔧 Development: Using hardcoded UUID for stats API');
                return '4d7c7b14-0344-4029-a489-28d5d24bc030'; // mubarak louis UUID
            }

            console.error('📡 Stats API: User UUID not found in any source');
            return null;
        } catch (error) {
            console.error('📡 Stats API: Error getting user UUID:', error);

            // Final fallback for development
            if (import.meta.env.DEV) {
                console.warn('🔧 Development: Using emergency fallback UUID');
                return '4d7c7b14-0344-4029-a489-28d5d24bc030';
            }

            return null;
        }
    }

    /**
     * Fetches user statistics from the backend
     * @param userUuid Optional user UUID. If not provided, tries to get from authenticated user
     * @returns Promise containing user stats data
     * @throws Error if the API request fails
     */
    async getUserStats(userUuid?: string): Promise<UserStats> {
        // Use provided UUID or get from authenticated user data
        const uuid = userUuid || this.getUserUuid();

        if (!uuid) {
            throw new Error('User UUID not found. Please ensure user is logged in or provide UUID.');
        }

        const apiUrl = `${this.baseUrl}/v1/stats/uuid/${uuid}`;

        try {
            console.log('📡 Stats API: Making request to:', apiUrl);
            console.log('📡 Stats API: User UUID:', uuid);

            const response = await axios.get(apiUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            console.log('📡 Stats API: Response status:', response.status);
            console.log('📡 Stats API: Response data:', response.data);

            const responseData: StatsApiResponse = response.data;

            if (!responseData.success) {
                throw new Error(responseData.message || 'Failed to fetch user stats');
            }

            const stats = responseData.stats;

            // Validate API response and provide helpful debugging
            const validation = this.validateApiFields(stats);

            // Fill in missing fields with appropriate defaults for development
            if (import.meta.env.DEV && validation.hasNewFeatures) {
                const enhancedStats = { ...stats };

                // Provide mock data for missing new features
                if (!stats.best_focus_time) {
                    enhancedStats.best_focus_time = '10:00am';
                }
                if (!stats.most_productive_day) {
                    enhancedStats.most_productive_day = new Date().toISOString().split('T')[0];
                }
                if (!stats.weekly_insight) {
                    enhancedStats.weekly_insight = {
                        monday: '01:30:00',
                        tuesday: '00:45:00',
                        wednesday: '02:15:00',
                        thursday: '01:00:00',
                        friday: '00:30:00',
                        saturday: '00:15:00',
                        sunday: '01:45:00',
                    };
                }
                if (!stats.achievements) {
                    enhancedStats.achievements = [
                        {
                            id: 1,
                            name: 'Focus Master',
                            description: 'Complete 10 focus sessions',
                            icon: 'Award',
                            target: 10,
                            current: 8,
                            achieved: false,
                            progress: 80,
                            level: 0,
                        },
                        {
                            id: 2,
                            name: 'Task Champion',
                            description: 'Complete 50 tasks',
                            icon: 'CheckCircle',
                            target: 50,
                            current: 25,
                            achieved: false,
                            progress: 50,
                            level: 0,
                        },
                        {
                            id: 3,
                            name: 'Early Bird',
                            description: '5 focus sessions before 9am',
                            icon: 'Zap',
                            target: 5,
                            current: 5,
                            achieved: true,
                            progress: 100,
                            level: 1,
                        },
                        {
                            id: 4,
                            name: 'Night Owl',
                            description: 'Complete 5 sessions after 9pm',
                            icon: 'Brain',
                            target: 5,
                            current: 3,
                            achieved: false,
                            progress: 60,
                            level: 0,
                        },
                        {
                            id: 5,
                            name: 'Productivity Guru',
                            description: 'Complete 100 tasks',
                            icon: 'Activity',
                            target: 100,
                            current: 75,
                            achieved: false,
                            progress: 75,
                            level: 0,
                        },
                        {
                            id: 6,
                            name: 'Knowledge Seeker',
                            description: 'Complete 3 courses',
                            icon: 'BookOpen',
                            target: 3,
                            current: 1,
                            achieved: false,
                            progress: 33,
                            level: 0,
                        },
                    ];
                }

                console.info('🚀 Enhanced stats with mock data for missing fields:', enhancedStats);
                return enhancedStats as UserStats;
            }

            return stats;
        } catch (error) {
            if (axios.isAxiosError(error)) {
                const status = error.response?.status;
                const message = error.response?.data?.message || error.message;

                switch (status) {
                    case 404:
                        // For new users, return empty stats instead of throwing an error
                        console.log('No stats found - returning empty stats for new user');
                        return {
                            total_time: '00:00:00',
                            best_focus_time: 'No data',
                            most_productive_day: 'No data',
                            weekly_insight: {
                                monday: '00:00:00',
                                tuesday: '00:00:00',
                                wednesday: '00:00:00',
                                thursday: '00:00:00',
                                friday: '00:00:00',
                                saturday: '00:00:00',
                                sunday: '00:00:00',
                            },
                            achievements: [],
                            average_efficiency: 0,
                            current_streak: 0,
                            longest_streak: 0,
                            total_sessions: 0,
                            completed_sessions: 0,
                            last_session_date: null,
                        };
                    case 429:
                        throw new Error('Too many requests. Please wait a moment and try again.');
                    case 500:
                        throw new Error('Server error. Our team has been notified and is working on a fix.');
                    case 503:
                        throw new Error('Service temporarily unavailable. Please try again later.');
                    default:
                        if (!navigator.onLine) {
                            throw new Error('No internet connection. Please check your network and try again.');
                        }
                        throw new Error(`API Error (${status || 'Unknown'}): ${message}`);
                }
            }

            // Handle network errors
            if ((error as any).code === 'NETWORK_ERROR' || !navigator.onLine) {
                throw new Error('Network error. Please check your internet connection and try again.');
            }

            throw new Error('An unexpected error occurred while fetching stats. Please try again later.');
        }
    }

    /**
     * Formats time from HH:MM:SS format to a human-readable string
     * @param timeString - Time in HH:MM:SS format
     * @returns Formatted time string (e.g., "2h 30m", "45m")
     */
    formatStudyTime(timeString: string): string {
        if (!timeString || timeString === '00:00:00') {
            return '0m';
        }

        const [hours, minutes] = timeString.split(':').map(Number);

        if (hours === 0) {
            return `${minutes}m`;
        }

        if (minutes === 0) {
            return `${hours}h`;
        }

        return `${hours}h ${minutes}m`;
    }

    /**
     * Calculates streak percentage based on a goal of 30 days
     * @param currentStreak - Current streak value
     * @param goal - Goal streak (default: 30 days)
     * @returns Percentage of goal achieved
     */
    calculateStreakPercentage(currentStreak: number, goal: number = 30): number {
        return Math.min(Math.round((currentStreak / goal) * 100), 100);
    }

    /**
     * Formats efficiency change (placeholder for future implementation)
     * @param efficiency - Current efficiency value
     * @returns Object with formatted text and color class
     */
    formatEfficiencyChange(efficiency: number): { text: string; colorClass: string } {
        // Placeholder implementation - could be enhanced with historical data
        const change = efficiency > 75 ? 5 : efficiency > 50 ? 0 : -3;
        const prefix = change > 0 ? '+' : '';
        const text = `${prefix}${change}%`;
        const colorClass =
            change > 0 ? 'text-green-500 dark:text-green-400' : change < 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-gray-400';

        return { text, colorClass };
    }

    /**
     * Formats the best focus time for display
     * @param timeString - Time string from API (e.g., "11:00pm", "09:00am")
     * @returns Formatted time string for UI display
     */
    formatBestFocusTime(timeString: string): string {
        if (!timeString || timeString.trim() === '') {
            return 'Not available';
        }

        // Convert 24-hour format to 12-hour format if needed
        try {
            // Handle both "HH:MMpm/am" and "HH:MM" formats
            const cleanTime = timeString.toLowerCase().trim();

            // If already in 12-hour format, return as-is with proper formatting
            if (cleanTime.includes('pm') || cleanTime.includes('am')) {
                const [time, period] = cleanTime.split(/([ap]m)/);
                const [hours, minutes] = time.split(':');
                return `${hours}:${minutes}${period.toUpperCase()}`;
            }

            // If in 24-hour format, convert to 12-hour
            const [hours, minutes] = cleanTime.split(':').map(Number);
            if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
                return timeString; // Return original if invalid
            }

            const period = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours === 0 ? 12 : hours > 12 ? hours - 12 : hours;
            return `${displayHours}:${minutes.toString().padStart(2, '0')}${period}`;
        } catch (error) {
            console.warn('Error formatting best focus time:', error);
            return timeString; // Return original string if formatting fails
        }
    }

    /**
     * Validates if all expected fields are present in the API response
     * @param stats - UserStats object from API
     * @returns Object with validation results and missing fields
     */
    validateApiFields(stats: any): { isValid: boolean; missingFields: string[]; hasNewFeatures: boolean } {
        const expectedFields = [
            'total_time',
            'best_focus_time',
            'most_productive_day',
            'weekly_insight',
            'achievements',
            'average_efficiency',
            'current_streak',
            'longest_streak',
            'total_sessions',
            'completed_sessions',
            'last_session_date',
        ];

        const missingFields = expectedFields.filter((field) => !(field in stats) || stats[field] === null || stats[field] === undefined);
        const hasNewFeatures =
            missingFields.includes('best_focus_time') ||
            missingFields.includes('most_productive_day') ||
            missingFields.includes('weekly_insight') ||
            missingFields.includes('achievements');

        if (import.meta.env.DEV && missingFields.length > 0) {
            console.group('🔍 API Field Validation');
            console.log('Expected fields:', expectedFields);
            console.log('Available fields:', Object.keys(stats));
            console.log('Missing fields:', missingFields);
            if (hasNewFeatures) {
                console.info(
                    '💡 New features detected! Backend may need updating for:',
                    missingFields.filter((f) => ['best_focus_time', 'most_productive_day', 'weekly_insight', 'achievements'].includes(f)),
                );
            }
            console.groupEnd();
        }

        return {
            isValid: missingFields.length === 0,
            missingFields,
            hasNewFeatures,
        };
    }

    /**
     * Formats weekly insights for display by converting each day's time to minutes
     * @param weeklyInsight - Weekly insight object from API
     * @returns Array of objects with day names and minutes for chart display
     */
    formatWeeklyInsights(weeklyInsight: WeeklyInsight): Array<{ day: string; minutes: number }> {
        console.log('📊 formatWeeklyInsights: Input data:', weeklyInsight);

        if (!weeklyInsight) {
            console.log('📊 formatWeeklyInsights: No weekly insight data, returning zeros');
            // Return default empty data
            return [
                { day: 'Monday', minutes: 0 },
                { day: 'Tuesday', minutes: 0 },
                { day: 'Wednesday', minutes: 0 },
                { day: 'Thursday', minutes: 0 },
                { day: 'Friday', minutes: 0 },
                { day: 'Saturday', minutes: 0 },
                { day: 'Sunday', minutes: 0 },
            ];
        }

        const convertTimeToMinutes = (timeString: string): number => {
            console.log(`📊 Converting time "${timeString}" to minutes`);

            if (!timeString || timeString === '00:00:00') {
                console.log(`📊 Time "${timeString}" is empty or zero, returning 0`);
                return 0;
            }

            try {
                const [hours, minutes, seconds] = timeString.split(':').map(Number);
                // Convert to decimal minutes: hours*60 + minutes + seconds/60
                const totalMinutes = hours * 60 + minutes + (seconds || 0) / 60;
                console.log(`📊 Converted "${timeString}" -> ${hours}h ${minutes}m ${seconds}s = ${totalMinutes} minutes`);
                return totalMinutes;
            } catch (error) {
                console.warn('📊 Error converting time to minutes:', timeString, error);
                return 0;
            }
        };

        const result = [
            { day: 'Monday', minutes: convertTimeToMinutes(weeklyInsight.monday) },
            { day: 'Tuesday', minutes: convertTimeToMinutes(weeklyInsight.tuesday) },
            { day: 'Wednesday', minutes: convertTimeToMinutes(weeklyInsight.wednesday) },
            { day: 'Thursday', minutes: convertTimeToMinutes(weeklyInsight.thursday) },
            { day: 'Friday', minutes: convertTimeToMinutes(weeklyInsight.friday) },
            { day: 'Saturday', minutes: convertTimeToMinutes(weeklyInsight.saturday) },
            { day: 'Sunday', minutes: convertTimeToMinutes(weeklyInsight.sunday) },
        ];

        console.log('📊 formatWeeklyInsights: Final result:', result);
        console.log(
            '📊 formatWeeklyInsights: Total minutes this week:',
            result.reduce((sum, day) => sum + day.minutes, 0),
        );

        return result;
    }

    /**
     * Formats achievements for display by calculating completion status
     * @param achievements - Achievements array from API
     * @returns Formatted achievements with proper icon mappings
     */
    formatAchievements(achievements: Achievement[]): Achievement[] {
        if (!achievements || !Array.isArray(achievements)) {
            // Return default achievements structure
            return [
                {
                    id: 1,
                    name: 'Focus Master',
                    description: 'Complete 10 focus sessions',
                    icon: 'Award',
                    target: 10,
                    current: 0,
                    achieved: false,
                    progress: 0,
                    level: 0,
                },
                {
                    id: 2,
                    name: 'Task Champion',
                    description: 'Complete 50 tasks',
                    icon: 'CheckCircle',
                    target: 50,
                    current: 0,
                    achieved: false,
                    progress: 0,
                    level: 0,
                },
                {
                    id: 3,
                    name: 'Early Bird',
                    description: '5 focus sessions before 9am',
                    icon: 'Zap',
                    target: 5,
                    current: 0,
                    achieved: false,
                    progress: 0,
                    level: 0,
                },
                {
                    id: 4,
                    name: 'Night Owl',
                    description: 'Complete 5 sessions after 9pm',
                    icon: 'Brain',
                    target: 5,
                    current: 0,
                    achieved: false,
                    progress: 0,
                    level: 0,
                },
                {
                    id: 5,
                    name: 'Productivity Guru',
                    description: 'Complete 100 tasks',
                    icon: 'Activity',
                    target: 100,
                    current: 0,
                    achieved: false,
                    progress: 0,
                    level: 0,
                },
                {
                    id: 6,
                    name: 'Knowledge Seeker',
                    description: 'Complete 3 courses',
                    icon: 'BookOpen',
                    target: 3,
                    current: 0,
                    achieved: false,
                    progress: 0,
                    level: 0,
                },
            ];
        }

        return achievements;
    }

    /**
     * Formats the most productive day for display
     * @param dateString - Date string from API (e.g., "2025-08-07")
     * @returns Formatted day string for UI display (e.g., "Tuesday")
     */
    formatMostProductiveDay(dateString: string): string {
        // Handle missing/undefined data with development fallback
        if (!dateString || dateString.trim() === '') {
            // In development, provide realistic mock data for better UX
            if (import.meta.env.DEV) {
                // Return a different day each time to simulate real data
                const mockDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                const randomDay = mockDays[Math.floor(Math.random() * mockDays.length)];
                return `${randomDay} (mock)`;
            }
            return 'Coming soon';
        }

        try {
            // Parse the date string
            const date = new Date(dateString);

            // Check if the date is valid
            if (isNaN(date.getTime())) {
                if (import.meta.env.DEV) {
                    console.warn('📅 Invalid date format for most productive day:', dateString);
                }
                return 'Invalid date';
            }

            // Get the day name
            const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const dayName = dayNames[date.getDay()];

            return dayName;
        } catch (error) {
            console.warn('Error formatting most productive day:', error);
            return import.meta.env.DEV ? 'Format error' : 'Coming soon';
        }
    }
}

// Export a singleton instance for use throughout the application
export const statsApiService = new StatsApiService();
