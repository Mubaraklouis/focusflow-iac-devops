<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-7 p-7">
            <!-- Modern glassy greeting section with light orange theme -->
            <div
                class="relative flex items-center overflow-hidden rounded-[1.25rem] border border-neutral-300/50 bg-neutral-100/80 shadow-sm backdrop-blur-2xl transition-all duration-300 hover:shadow-md dark:border-neutral-700/30 dark:bg-neutral-900/80"
            >
                <!-- Glassmorphism effects -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-transparent dark:from-neutral-800/20" />
                <div
                    class="from-primary-100/40 dark:from-primary-500/10 absolute right-0 top-0 h-1/2 w-1/3 rounded-bl-full bg-gradient-to-br to-transparent opacity-50"
                />

                <div
                    class="relative z-10 flex w-full flex-col items-start justify-between gap-4 p-6 dark:bg-border/50 md:flex-row md:items-center md:p-5 lg:p-6"
                >
                    <!-- Profile Section -->
                    <div class="flex min-w-0 flex-1 items-center gap-4">
                        <div
                            class="shadow-xs relative h-14 w-14 rounded-full bg-gradient-to-br from-neutral-100/90 to-neutral-50/80 p-[2px] dark:from-neutral-800/70 dark:to-neutral-900/80"
                        >
                            <Avatar class="h-full w-full border-[1.5px] border-neutral-200/30 dark:border-white/10">
                                <AvatarImage :src="user?.profile_photo_url" alt="Profile photo" />
                                <AvatarFallback
                                    class="bg-neutral-100 text-[15px] font-medium text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                                >
                                    {{ (user?.name ?? 'User').substring(0, 2).toUpperCase() }}
                                </AvatarFallback>
                            </Avatar>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h1 class="truncate text-2xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100 md:text-[26px]">
                                <span class="text-primary-600 dark:text-primary-400">Good {{ formatTimeOfDay() }},</span><br class="sm:hidden" />
                                <span class="text-neutral-700 dark:text-neutral-200">{{ user?.name?.split(' ')[0] ?? 'User' }}</span>
                            </h1>
                            <p class="mt-1 text-[15px] leading-snug text-neutral-600/90 dark:text-neutral-400/90">
                                {{ dailyGreeting }}
                            </p>
                        </div>
                    </div>

                    <!-- Date Section -->
                    <div class="flex w-full flex-col items-stretch gap-3 sm:w-auto sm:flex-row md:w-full md:flex-col lg:w-auto lg:flex-row">
                        <div
                            class="shadow-xs flex items-center gap-2 rounded-xl border border-neutral-200/60 bg-white/70 px-4 py-2.5 backdrop-blur-sm dark:border-neutral-700/50 dark:bg-neutral-800/80"
                        >
                            <Calendar class="text-primary-500 dark:text-primary-400 h-4 w-4 flex-shrink-0" />
                            <span class="whitespace-nowrap text-[15px] font-medium text-neutral-700 dark:text-neutral-300">
                                {{ todayFormatted }}
                            </span>
                        </div>

                        <Button
                            variant="ghost"
                            size="sm"
                            class="shadow-xs h-auto rounded-xl border border-neutral-200/60 bg-transparent px-4 py-2.5 backdrop-blur-sm transition-colors hover:bg-neutral-100/60 dark:border-neutral-700/50 dark:hover:bg-neutral-800/80"
                        >
                            <Calendar class="text-primary-500 dark:text-primary-400 mr-2 h-4 w-4" />
                            <span class="text-[15px] font-medium text-neutral-700 dark:text-neutral-300">Focus Calendar</span>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Combined Stats and Streak Section -->
            <div class="grid gap-7 md:grid-cols-10 lg:grid-cols-12">
                <!-- Stats cards - expanded layout -->
                <div class="grid gap-6 dark:text-white md:col-span-10 md:grid-cols-4 lg:col-span-12 lg:grid-cols-4">
                    <Card
                        class="card-hover-effect group relative min-h-[120px] overflow-hidden border-border/50 shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:text-white dark:hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-medium">Total Study Time</CardTitle>
                                <Timer class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-foreground">
                                        <span v-if="statsLoading">Loading...</span>
                                        <span v-else-if="statsError" class="text-red-500" :title="formatErrorMessage">Error</span>
                                        <span v-else-if="isNewUser" class="text-muted-foreground">0m</span>
                                        <span v-else>{{ stats.formattedStudyTime || stats.totalStudyTime || 'No data' }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span v-if="statsError" class="text-red-400">{{ formatErrorMessage }}</span>
                                        <span v-else-if="isNewUser" class="text-blue-500">Start your first focus session!</span>
                                        <span v-else>Time focused in sessions ({{ stats.totalSessions || 0 }} total)</span>
                                    </p>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <Timer class="h-6 w-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="card-hover-effect group relative min-h-[120px] overflow-hidden border-border/50 shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:text-white dark:hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-medium">Current Streak</CardTitle>
                                <Flame class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-foreground">
                                        <span v-if="statsLoading">Loading...</span>
                                        <span v-else-if="statsError" class="text-red-500" :title="formatErrorMessage">Error</span>
                                        <span v-else-if="isNewUser" class="text-muted-foreground">0 days</span>
                                        <span v-else>{{ stats.streak ?? 0 }} days</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span v-if="!statsLoading && !statsError && stats.streak > 0"
                                            >{{ stats.streakPercentage }}% of your goal</span
                                        >
                                        <span v-else-if="isNewUser" class="text-blue-500">Build your focus streak</span>
                                    </p>
                                </div>
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400"
                                >
                                    <Flame class="h-6 w-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="card-hover-effect group relative min-h-[120px] overflow-hidden border-border/50 shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:text-white dark:hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-medium">Avg Efficiency</CardTitle>
                                <Zap class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-foreground">
                                        <span v-if="statsLoading">Loading...</span>
                                        <span v-else-if="statsError" class="text-red-500" :title="formatErrorMessage">Error</span>
                                        <span v-else-if="isNewUser" class="text-muted-foreground">--%</span>
                                        <span v-else>{{ Math.round(stats.efficiency ?? 0) }}%</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span
                                            v-if="!statsLoading && !statsError && stats.efficiency > 0"
                                            :class="stats.efficiencyChange.colorClass"
                                            >{{ stats.efficiencyChange.text }}</span
                                        >
                                        <span v-else-if="isNewUser" class="text-blue-500">Complete sessions to track efficiency</span>
                                    </p>
                                </div>
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400"
                                >
                                    <Activity class="h-6 w-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        class="card-hover-effect group relative min-h-[120px] overflow-hidden border-border/50 shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:text-white dark:hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-medium">Sessions Completed</CardTitle>
                                <CheckCircle class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-foreground">
                                        <span v-if="statsLoading">Loading...</span>
                                        <span v-else-if="statsError" class="text-red-500" :title="formatErrorMessage">Error</span>
                                        <span v-else-if="isNewUser" class="text-muted-foreground">0</span>
                                        <span v-else>{{ stats.tasksCompleted ?? 0 }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span
                                            v-if="!statsLoading && !statsError && stats.tasksCompleted > 0"
                                            :class="stats.tasksCompletedChange.colorClass"
                                            >{{ stats.tasksCompletedChange.text }}</span
                                        >
                                        <span v-else-if="!statsLoading && !statsError && stats.tasksCompleted === 0" class="text-blue-500">{{
                                            stats.tasksCompletedChange.text
                                        }}</span>
                                    </p>
                                </div>
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400"
                                >
                                    <CheckCircle class="h-6 w-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Weekly Activities and Learning Insights Section -->
                <div class="grid gap-7 md:col-span-10 md:grid-cols-1 lg:col-span-12 lg:grid-cols-2">
                    <!-- Weekly Activity Card -->
                    <Card class="min-h-[280px] border border-border/50 shadow-sm transition-all hover:shadow-md dark:bg-card/95">
                        <CardHeader class="flex flex-row items-center justify-between border-b border-border/40 bg-muted/30 pb-3 dark:bg-muted/20">
                            <div class="space-y-1">
                                <h3 class="text-lg font-medium text-foreground">Weekly Activity</h3>
                                <p class="text-sm text-muted-foreground dark:text-white/80">Your focus sessions this week</p>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-4">
                            <div class="space-y-4">
                                <!-- Activity bars -->
                                <!-- Weekly Activity Chart -->
                                <div v-if="totalStudyTime > 0" class="flex h-40 w-full items-end gap-1.5">
                                    <div
                                        v-for="(day, index) in unwrappedFocusMetrics?.weeklyActivity || []"
                                        :key="index"
                                        class="flex-1 rounded-t bg-primary/10 transition-all hover:bg-primary/20 dark:bg-primary/20 dark:hover:bg-primary/30"
                                        :class="isActiveDay(day) ? 'bg-primary/20 dark:bg-primary/30' : ''"
                                        :style="{ height: `${getChartHeight(day.minutes, maxWeeklyMinutes)}%` }"
                                        :title="`${day.day}: ${day.minutes > 0 ? Math.round(day.minutes) + ' minutes' : 'No activity'}`"
                                    >
                                        <div class="flex h-full flex-col justify-end">
                                            <div
                                                class="rounded-t bg-primary dark:bg-primary/80"
                                                :class="isActiveDay(day) ? 'bg-primary dark:bg-primary/80' : 'bg-primary/30 dark:bg-primary/50'"
                                                :style="{ height: `${getChartHeight(day.minutes, maxWeeklyMinutes)}%` }"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty state for new users or no data -->
                                <div
                                    v-else
                                    class="flex h-40 w-full flex-col items-center justify-center rounded-lg border-2 border-dashed border-border/50"
                                >
                                    <Activity class="h-8 w-8 text-muted-foreground opacity-40" />
                                    <p class="mt-2 text-sm font-medium text-muted-foreground">No activity yet</p>
                                    <p class="text-xs text-muted-foreground">Your weekly focus time will appear here</p>
                                    <Button variant="ghost" size="sm" class="mt-2 text-xs" @click="startFocusSession"> Start focusing </Button>
                                </div>

                                <div class="flex justify-between text-[10px] text-muted-foreground dark:text-white/80">
                                    <span
                                        v-for="day in unwrappedFocusMetrics?.weeklyActivity || []"
                                        :key="day.day"
                                        :title="`${day.day}: ${day.minutes > 0 ? Math.round(day.minutes * 10) / 10 + ' minutes' : 'No activity'}`"
                                    >
                                        {{ day.day.substring(0, 3) }}
                                    </span>
                                </div>

                                <div class="space-y-3 pt-1">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium dark:text-white">Total study time</span>
                                        <span class="dark:text-white"
                                            >{{ Math.round((totalStudyTime / 60) * 10) / 10 }}/{{ unwrappedFocusMetrics?.weeklyGoal || 0 }}h</span
                                        >
                                    </div>

                                    <div class="h-2.5 w-full rounded-full bg-muted/70 dark:bg-muted/40">
                                        <div
                                            class="h-2.5 rounded-full bg-gradient-to-r from-primary/90 to-primary"
                                            :style="{
                                                width: `${Math.min((totalStudyTime / 60 / (unwrappedFocusMetrics?.weeklyGoal || 1)) * 100, 100)}%`,
                                            }"
                                        ></div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between text-sm">
                                        <div class="flex items-center">
                                            <div class="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                                                <TrendingUp class="h-4 w-4 text-primary" />
                                            </div>
                                            <span class="dark:text-white">Weekly progress</span>
                                        </div>
                                        <Badge variant="outline" class="border-primary/30 bg-primary/10 px-2">
                                            <ArrowUp class="mr-1 h-3 w-3 text-green-500" />
                                            <!-- <span class="dark:text-white">+15m</span> -->
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Learning Insights Card -->
                    <Card class="card-hover-effect min-h-[320px] border border-border/50 shadow-sm transition-all hover:shadow-md dark:bg-card/95">
                        <CardHeader class="border-b border-border/40 bg-muted/30 pb-4 dark:bg-muted/20">
                            <div class="flex items-center justify-between">
                                <div class="space-y-1">
                                    <h3 class="text-lg font-semibold text-foreground">Learning Insights</h3>
                                    <p class="text-sm text-muted-foreground dark:text-white/80">Your learning patterns and progress</p>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-6">
                            <div class="space-y-5">
                                <div
                                    class="flex items-center justify-between rounded-md bg-accent/40 p-3 transition-colors hover:bg-accent/50 dark:bg-accent/30"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="mr-4 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary"
                                        >
                                            <Brain class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="font-medium">Best Focus Time</p>
                                            <p class="text-sm text-muted-foreground dark:text-white/80">
                                                {{
                                                    stats.bestFocusTime === 'Start focusing to see your best time'
                                                        ? 'Track your patterns'
                                                        : stats.bestFocusTime
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                        <!-- <span class="text-sm text-muted-foreground dark:text-white/80">85% efficient</span> -->
                                    </div>
                                </div>

                                <!-- Feature 002: Most Productive Day
                                     Status: Frontend implemented ✅ | Backend pending ⏳

                                     This section shows the day of the week when the user completed
                                     the most tasks. Currently displays mock data until backend
                                     includes 'most_productive_day' field in /api/v1/stats response.

                                     Expected API format: "most_productive_day": "2025-08-07"
                                     Will display as: "Tuesday"
                                -->
                                <div
                                    class="flex items-center justify-between rounded-md bg-accent/40 p-3 transition-colors hover:bg-accent/50 dark:bg-accent/30"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400"
                                        >
                                            <Flame class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="font-medium">Most Productive Day</p>
                                            <p class="text-sm text-muted-foreground dark:text-white/80">
                                                {{
                                                    stats.mostProductiveDay === 'Complete sessions to find your best day'
                                                        ? 'Discover your rhythm'
                                                        : stats.mostProductiveDay
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                                        <!-- Note: tasksCompleted represents completed_sessions from API -->
                                        <span class="text-sm text-muted-foreground dark:text-white/80"
                                            >{{ stats.tasksCompleted }} {{ stats.tasksCompleted === 1 ? 'task' : 'tasks' }} completed</span
                                        >
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between rounded-md bg-accent/40 p-3 transition-colors hover:bg-accent/50 dark:bg-accent/30"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="mr-4 flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400"
                                        >
                                            <BookOpen class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="font-medium">Learning Preference</p>
                                            <p class="text-sm text-muted-foreground dark:text-white/80">Visual content</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                        <!-- <span class="text-sm text-muted-foreground dark:text-white/80">70% comprehension</span> -->
                                    </div>
                                </div>

                                <!-- Daily Learning Tip -->
                                <!-- <div class="mt-3 p-4 rounded-lg bg-gradient-to-br from-primary/15 via-accent/40 to-accent/30 dark:from-primary/30 dark:via-accent/40 dark:to-accent/30 border border-primary/30 shadow-sm">
                                    <div class="flex">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-primary/20 text-primary mr-4">
                                            <Zap class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-base">Daily Learning Tip</p>
                                            <p class="text-sm text-muted-foreground mt-2">
                                                Taking short 5-minute breaks every 25 minutes of focused study can improve retention by up to 30%.
                                            </p>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Second row with 3-column layout -->
            <div class="grid gap-7 lg:grid-cols-7">
                <!-- Left column with achievements and recent sessions -->
                <div class="min-h-0 space-y-7 lg:col-span-3">
                    <!-- Achievements/badges section -->
                    <Card class="min-h-fit border-border/50 shadow-sm dark:text-white" style="height: auto; overflow: visible">
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-foreground">Your Achievements</h3>
                                <Badge variant="outline" class="border-primary/30 bg-primary/10">
                                    <Award class="mr-1 h-3.5 w-3.5 text-primary" />
                                    <span class="dark:text-white">
                                        {{ isNewUser ? '0/3' : `${(badges || []).filter((b) => b.achieved).length}/${(badges || []).length}` }}
                                    </span>
                                </Badge>
                            </div>
                        </CardHeader>
                        <CardContent class="overflow-visible" style="max-height: none; overflow: visible">
                            <div class="grid grid-cols-3 gap-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-3">
                                <div
                                    v-for="badge in badges || []"
                                    :key="badge.id"
                                    class="group relative flex min-h-[80px] flex-col items-center justify-center rounded-lg p-2 transition-all hover:bg-accent/50 dark:hover:bg-accent/20"
                                    :class="badge.achieved ? 'bg-primary/5 dark:bg-primary/10' : 'bg-muted/20 dark:bg-muted/10'"
                                >
                                    <div
                                        class="relative mb-1 flex h-10 w-10 items-center justify-center rounded-full"
                                        :class="
                                            badge.achieved
                                                ? 'bg-primary/10 text-primary dark:bg-primary/20'
                                                : 'bg-muted/30 text-muted-foreground dark:bg-muted/20'
                                        "
                                    >
                                        <span
                                            v-if="badge.achieved"
                                            class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-green-500 text-white"
                                        >
                                            <CheckCircle class="h-2 w-2" />
                                        </span>
                                        <component :is="getIconComponent(badge.icon)" class="h-5 w-5" />

                                        <!-- Animated border on hover for achieved badges -->
                                        <div
                                            v-if="badge.achieved"
                                            class="absolute inset-0 animate-pulse rounded-full border border-primary/30 opacity-0 transition-opacity group-hover:opacity-100"
                                        ></div>
                                    </div>
                                    <span class="text-center text-xs font-medium text-foreground">{{ badge.name }}</span>
                                    <span class="text-center text-[10px] text-muted-foreground">{{ badge.achieved ? 'Unlocked' : 'Locked' }}</span>

                                    <!-- Level indicator for achieved badges -->
                                    <div v-if="badge.achieved && badge.level" class="mt-1 flex items-center">
                                        <div class="flex">
                                            <div
                                                v-for="n in 3"
                                                :key="n"
                                                class="mx-0.5 h-1 w-4 rounded-full"
                                                :class="n <= badge.level ? 'bg-primary dark:bg-primary/80' : 'bg-primary/20 dark:bg-primary/20'"
                                            ></div>
                                        </div>
                                        <span class="ml-1 text-[10px] font-medium text-primary dark:text-primary-foreground">L{{ badge.level }}</span>
                                    </div>

                                    <!-- Progress indicator for locked badges -->
                                    <div v-if="!badge.achieved && badge.progress" class="mt-1 w-full px-1">
                                        <div class="relative h-1 w-full rounded-full bg-muted/30">
                                            <div
                                                class="absolute left-0 top-0 h-1 rounded-full bg-primary/40"
                                                :style="`width: ${badge.progress}%`"
                                            ></div>
                                        </div>
                                        <div class="mt-0.5 text-center text-[9px] text-muted-foreground">
                                            <span>{{ badge.progress }}%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions Section -->
                    <Card class="card-hover-effect border-border/50 shadow-sm dark:text-white">
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-foreground">Quick Actions</h3>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-2 gap-2">
                                <Button
                                    variant="outline"
                                    class="h-10 justify-start border-border bg-accent/10 dark:bg-accent/5"
                                    @click="startFocusSession"
                                >
                                    <div class="flex items-center">
                                        <div class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary">
                                            <Timer class="h-4 w-4" />
                                        </div>
                                        <span>Start Focus Session</span>
                                    </div>
                                </Button>
                                <Button
                                    variant="outline"
                                    class="h-10 justify-start border-border bg-accent/10 dark:bg-accent/5"
                                    @click="continueLearning"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-400"
                                        >
                                            <BookOpen class="h-4 w-4" />
                                        </div>
                                        <span>Continue Learning</span>
                                    </div>
                                </Button>
                                <Button variant="outline" class="h-10 justify-start border-border bg-accent/10 dark:bg-accent/5" @click="viewTasks">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400"
                                        >
                                            <CheckCircle class="h-4 w-4" />
                                        </div>
                                        <span>View Tasks</span>
                                    </div>
                                </Button>
                                <Button variant="outline" class="h-10 justify-start border-border bg-accent/10 dark:bg-accent/5" @click="setGoals">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-2 flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400"
                                        >
                                            <Award class="h-4 w-4" />
                                        </div>
                                        <span>Set Goals</span>
                                    </div>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent focus sessions -->
                    <Card class="border-border/50 shadow-sm">
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-foreground">Recent Focus Sessions</h3>
                                <Button variant="ghost" size="sm" class="h-8 px-2 dark:text-white" @click="router.visit(route('focus.sessions'))">
                                    View all
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div v-if="sessionsLoading" class="flex items-center justify-center py-8">
                                <div class="text-sm text-muted-foreground">Loading recent sessions...</div>
                            </div>
                            <div v-else-if="sessionsError" class="flex items-center justify-center py-8">
                                <div class="text-sm text-red-500">{{ sessionsError }}</div>
                            </div>
                            <div v-else-if="(recentFocusSessions || []).length > 0">
                                <div
                                    v-for="(session, index) in recentFocusSessions || []"
                                    :key="session.id || index"
                                    class="border-b border-border transition-colors last:border-0 hover:bg-accent/40 dark:hover:bg-accent/20"
                                >
                                    <div class="flex items-start justify-between p-4">
                                        <div class="flex items-start space-x-4">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary dark:bg-primary/20 dark:text-white"
                                            >
                                                <Timer class="h-4 w-4 dark:text-white" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-foreground">{{ session.title }}</p>
                                                <div class="mt-1 flex text-xs dark:text-white">
                                                    <span class="flex items-center">
                                                        <Timer class="mr-1 h-3 w-3 dark:text-white" />
                                                        {{ session.pomodoroCount || 0 }} pomodoros
                                                    </span>
                                                    <span class="mx-2">•</span>
                                                    <span class="flex items-center">
                                                        <CheckCircle class="mr-1 h-3 w-3 dark:text-white" />
                                                        {{ session.tasksCompleted || 0 }} tasks
                                                    </span>
                                                    <span class="mx-2">•</span>
                                                    <span class="text-muted-foreground">
                                                        {{ formatSessionDate(session.date) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="mb-2 text-sm font-medium text-foreground">
                                                {{ formatSeconds(session.duration) }}
                                            </div>
                                            <Badge
                                                variant="outline"
                                                class="text-xs"
                                                :class="
                                                    session.efficiency > 80
                                                        ? 'border-green-300 bg-green-50 text-green-700'
                                                        : session.efficiency > 50
                                                          ? 'border-yellow-300 bg-yellow-50 text-yellow-700'
                                                          : 'border-red-300 bg-red-50 text-red-700'
                                                "
                                            >
                                                {{ session.efficiency || 0 }}% complete
                                            </Badge>
                                            <div class="mt-1 text-xs capitalize text-muted-foreground">
                                                {{ session.status }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="session.notes" class="-mt-1 px-4 pb-3">
                                        <p class="text-xs italic dark:text-white">"{{ session.notes }}"</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-8">
                                <Timer class="h-10 w-10 text-muted-foreground opacity-40" />
                                <p class="mt-2 text-sm text-muted-foreground">Welcome to FocusFlow!</p>
                                <p class="text-xs text-muted-foreground">Start your first focus session to begin tracking your productivity</p>
                                <Button variant="outline" size="sm" class="mt-4 border-border" @click="startFocusSession">
                                    Start Your First Session
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right column with courses -->
                <div class="space-y-7 lg:col-span-4">
                    <!-- Courses section -->
                    <Card class="overflow-hidden border-border/50 shadow-sm dark:text-white">
                        <CardHeader class="border-b border-border/30 bg-muted/20 pb-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-foreground">Your Courses</h3>
                                    <Badge v-if="coursesInProgress.length > 0" variant="secondary" class="h-5 px-2 text-xs">
                                        {{ coursesInProgress.length }}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="fetchCourses" :disabled="coursesLoading">
                                        <RotateCcw :class="`h-3.5 w-3.5 ${coursesLoading ? 'animate-spin' : ''}`" />
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="hidden h-8 border-border/50 sm:flex"
                                        @click="router?.visit ? router.visit(route('courses.index')) : null"
                                    >
                                        <Plus class="mr-1 h-3.5 w-3.5" />
                                        Browse
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        class="h-8 w-8 p-0 sm:hidden"
                                        @click="router?.visit ? router.visit(route('courses.index')) : null"
                                    >
                                        <Plus class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>
                            <!-- Course statistics -->
                            <div v-if="coursesInProgress.length > 0 && !coursesLoading" class="mt-3 flex gap-4 text-xs text-muted-foreground">
                                <div class="flex items-center gap-1">
                                    <CheckCircle class="h-3 w-3 text-green-500" />
                                    <span>{{ completedCoursesCount }} completed</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Timer class="h-3 w-3 text-blue-500" />
                                    <span>{{ inProgressCoursesCount }} in progress</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Brain class="h-3 w-3 text-purple-500" />
                                    <span>{{ averageProgress }}% avg progress</span>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="p-0">
                            <Tabs defaultValue="inProgress" class="w-full">
                                <div class="border-b border-border/30 px-4">
                                    <TabsList class="h-10 gap-4 bg-transparent p-0">
                                        <TabsTrigger value="inProgress" variant="underline" class="h-10 rounded-none"> Your Courses </TabsTrigger>
                                    </TabsList>
                                </div>
                                <TabsContent value="inProgress" class="m-0 focus-visible:outline-none focus-visible:ring-0">
                                    <!-- Loading state -->
                                    <div v-if="coursesLoading" class="space-y-4 p-4">
                                        <div v-for="i in 3" :key="i" class="animate-pulse">
                                            <div class="flex items-center gap-4">
                                                <div class="h-14 w-14 rounded-full bg-muted"></div>
                                                <div class="flex-1 space-y-2">
                                                    <div class="h-4 w-3/4 rounded bg-muted"></div>
                                                    <div class="h-3 w-1/2 rounded bg-muted"></div>
                                                    <div class="h-2 w-full rounded bg-muted"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Error state -->
                                    <div v-else-if="coursesError" class="flex flex-col items-center justify-center py-8">
                                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                                            <AlertCircle class="h-6 w-6 text-red-500" />
                                        </div>
                                        <div class="text-sm font-medium text-red-600 dark:text-red-400">{{ coursesError }}</div>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="mt-3 border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20"
                                            @click="fetchCourses"
                                        >
                                            Try Again
                                        </Button>
                                    </div>

                                    <!-- Courses list -->
                                    <div v-else class="grid gap-0 divide-y divide-border/30">
                                        <div
                                            v-for="course in coursesInProgress || []"
                                            :key="course.id"
                                            class="transition-colors hover:bg-accent/30 dark:hover:bg-accent/10"
                                        >
                                            <div class="p-4">
                                                <div class="flex items-center gap-3 sm:gap-4">
                                                    <!-- Course icon -->
                                                    <div
                                                        class="relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full border border-border/30 shadow-sm sm:h-14 sm:w-14"
                                                    >
                                                        <div class="h-full w-full" :style="`background-color: ${getCourseColor(course.id)}`">
                                                            <div class="absolute inset-0 flex items-center justify-center font-semibold text-white">
                                                                {{ getCourseInitials(course.title) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Course details -->
                                                    <div class="min-w-0 flex-1">
                                                        <!-- Title and badges -->
                                                        <div class="mb-1 flex items-center gap-2">
                                                            <h3 class="truncate text-base font-medium text-foreground">{{ course.title }}</h3>
                                                            <Badge
                                                                v-if="course.isNew"
                                                                variant="secondary"
                                                                class="h-5 border-blue-200 bg-blue-100 px-1.5 py-0 text-xs text-blue-700 dark:border-blue-800/50 dark:bg-blue-900/40 dark:text-blue-400"
                                                            >
                                                                New
                                                            </Badge>
                                                            <Badge
                                                                v-if="course.difficulty"
                                                                variant="outline"
                                                                :class="{
                                                                    'border-green-300 bg-green-50 text-green-700 dark:border-green-700 dark:bg-green-900/20 dark:text-green-400':
                                                                        course.difficulty === 'Beginner',
                                                                    'border-yellow-300 bg-yellow-50 text-yellow-700 dark:border-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400':
                                                                        course.difficulty === 'Intermediate',
                                                                    'border-red-300 bg-red-50 text-red-700 dark:border-red-700 dark:bg-red-900/20 dark:text-red-400':
                                                                        course.difficulty === 'Advanced',
                                                                }"
                                                                class="h-5 px-1.5 py-0 text-xs"
                                                            >
                                                                {{ course.difficulty }}
                                                            </Badge>
                                                        </div>

                                                        <!-- Course description -->
                                                        <p v-if="course.description" class="mb-2 line-clamp-1 text-xs text-muted-foreground">
                                                            {{ course.description }}
                                                        </p>

                                                        <!-- Progress -->
                                                        <div class="mb-3 mt-2 w-full">
                                                            <div class="mb-1.5 flex justify-between text-xs">
                                                                <div class="text-muted-foreground">
                                                                    Progress: <span class="font-medium text-foreground">{{ course.progress }}%</span>
                                                                </div>
                                                                <div class="text-muted-foreground">{{ course.timeLeft }} remaining</div>
                                                            </div>
                                                            <div class="relative h-1.5 w-full rounded-full bg-muted/50 dark:bg-muted/30">
                                                                <div
                                                                    class="absolute left-0 top-0 h-1.5 rounded-full bg-gradient-to-r from-primary/90 to-primary"
                                                                    :style="`width: ${course.progress || 0}%`"
                                                                ></div>
                                                            </div>
                                                        </div>

                                                        <!-- Action buttons and stats -->
                                                        <div class="flex items-center justify-between">
                                                            <div
                                                                class="flex flex-col gap-1 text-xs text-muted-foreground sm:flex-row sm:items-center sm:gap-3"
                                                            >
                                                                <div class="flex items-center">
                                                                    <BookOpen class="mr-1.5 h-3.5 w-3.5 text-primary/70" />
                                                                    <span class="max-w-[120px] truncate sm:max-w-[150px]">{{
                                                                        course.nextLesson
                                                                    }}</span>
                                                                </div>
                                                                <div v-if="course.progress > 0" class="flex items-center">
                                                                    <CheckCircle class="mr-1 h-3 w-3 text-green-500" />
                                                                    <span>{{ course.progress }}%</span>
                                                                </div>
                                                            </div>
                                                            <Button
                                                                variant="ghost"
                                                                size="sm"
                                                                class="h-7 flex-shrink-0 gap-1 text-xs hover:bg-primary/10 hover:text-primary"
                                                                @click="
                                                                    router?.visit ? router.visit(route('courses.detail') + '?id=' + course.id) : null
                                                                "
                                                            >
                                                                <span class="hidden sm:inline">{{
                                                                    course.progress >= 100 ? 'Review' : 'Continue'
                                                                }}</span>
                                                                <span class="sm:hidden">{{ course.progress >= 100 ? 'Review' : 'Go' }}</span>
                                                                <ArrowRight class="h-3 w-3" />
                                                            </Button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <!-- Empty state -->
                                    <div
                                        v-if="!coursesLoading && !coursesError && (coursesInProgress || []).length === 0"
                                        class="flex flex-col items-center justify-center py-12"
                                    >
                                        <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 dark:bg-primary/20">
                                            <BookOpen class="h-6 w-6 text-primary opacity-80 dark:text-primary-foreground" />
                                        </div>
                                        <h3 class="text-lg font-medium text-foreground">No courses found</h3>
                                        <p class="mt-1 max-w-sm text-center text-sm text-muted-foreground">
                                            Start your learning journey by exploring our course catalog.
                                        </p>
                                        <div class="mt-4 flex gap-2">
                                            <Button variant="default" size="sm" @click="router?.visit ? router.visit(route('courses.index')) : null">
                                                <Plus class="mr-1 h-3 w-3" />
                                                Browse Courses
                                            </Button>
                                            <Button variant="outline" size="sm" @click="fetchCourses">
                                                <RotateCcw class="mr-1 h-3 w-3" />
                                                Refresh
                                            </Button>
                                        </div>
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Activity,
    AlertCircle,
    ArrowRight,
    Award,
    BookOpen,
    Brain,
    Calendar,
    CheckCircle,
    Flame,
    Plus,
    RotateCcw,
    Timer,
    Zap,
} from 'lucide-vue-next';

// Import stats composable and service
import { useGetStatsComposable } from '@/composables/useGetStatsComposable';
import { statsApiService } from '@/services/statsApiService';

// Sample breadcrumbs
const breadcrumbs = [
    { title: 'Home', href: '/' },
    { title: 'Dashboard', href: '/dashboard', current: true },
];

// Add a type assertion for usePage().props to avoid "Object is of type 'unknown'" error
const user = computed(() => {
    const userData = (usePage().props as unknown as { auth: { user: any } }).auth.user;
    return userData;
});

// Sample stats data (props removed as not needed with API integration)

// Initialize stats composable
// Import stats composable and service
const {
    stats: userStats,
    isLoading: statsLoading,
    error: statsError,
    formattedStudyTime,
    formattedEfficiencyChange,
    formattedTasksChange,
    formattedBestFocusTime,
    formattedMostProductiveDay,
    formattedWeeklyInsights,
    formattedAchievements,
} = useGetStatsComposable();


// Computed property for stats data with fallbacks
const stats = computed(() => {
    const result = {
        totalStudyTime: userStats.value?.total_time || '00:00:00',
        formattedStudyTime: formattedStudyTime.value || '0m',
        streak: userStats.value?.current_streak ?? 0,
        streakPercentage:
            userStats.value && userStats.value.current_streak > 0 ? statsApiService.calculateStreakPercentage(userStats.value.current_streak) : 0,
        efficiency: userStats.value?.average_efficiency ?? 0,
        efficiencyChange: formattedEfficiencyChange.value,
        tasksCompleted: userStats.value?.completed_sessions ?? 0,
        tasksCompletedChange: formattedTasksChange.value,
        bestFocusTime: formattedBestFocusTime.value,
        mostProductiveDay: formattedMostProductiveDay.value,
        totalSessions: userStats.value?.total_sessions ?? 0,
        rawApiData: userStats.value,
    };

    return result;
});

// Computed property to check if user is new (no data yet)
const isNewUser = computed(() => {
    // Only consider user new if they have NO sessions AND NO time - streak can be 0 but still have data
    const result =
        !userStats.value || userStats.value.total_sessions === 0 || userStats.value.total_time === '00:00:00' || !userStats.value.total_time;

    return result;
});

// Computed property to check if we're in development mode
const isDev = computed(() => {
    return import.meta.env.DEV;
});

// Manual refresh function for debugging and user-initiated refresh
const refreshStats = async () => {
    console.log('🔄 Manual stats refresh initiated');
    try {
        await fetchStats();
        console.log('✅ Manual stats refresh completed successfully');
    } catch (error) {
        console.error('❌ Manual stats refresh failed:', error);
    }
};

// Better error message formatting
const formatErrorMessage = computed(() => {
    if (!statsError.value) return null;

    if (statsError.value.includes('401')) {
        return 'Please log in to view your stats';
    } else if (statsError.value.includes('404')) {
        return 'Stats endpoint not found - check backend';
    } else if (statsError.value.includes('500')) {
        return 'Server error - check Laravel logs';
    } else if (statsError.value.includes('Network')) {
        return 'Connection error - check internet';
    }

    return statsError.value;
});

// Debug info for troubleshooting UUID-based stats API
if (import.meta.env.DEV) {
    console.log('📊 UUID-Based Stats Debug Info:', {
        hasUser: !!user.value,
        userId: user.value?.id,
        userUuid: user.value?.uuid,
        userName: user.value?.name,
        newApiEndpoint: user.value?.uuid ? `/api/v1/stats/uuid/${user.value.uuid}` : 'NO UUID - ENDPOINT UNAVAILABLE',
        oldApiEndpoint: '/api/v1/stats (deprecated)',
        authenticationRequired: false,
        uuidSource: 'Inertia.js page props',
    });

    // Test UUID availability every 2 seconds in development
    setInterval(() => {
        const currentUuid = user.value?.uuid;
        if (currentUuid) {
            console.log('✅ UUID Still Available:', currentUuid);
        } else {
            console.warn('⚠️ UUID Lost or Not Available');
        }
    }, 2000);
}

// Focus metrics computed from API data
const focusMetrics = computed(() => {
    console.log('🔍 focusMetrics: Computation started');
    console.log('🔍 focusMetrics: formattedWeeklyInsights.value:', formattedWeeklyInsights.value);

    const weeklyData = formattedWeeklyInsights.value.map((day) => ({
        day: day.day.substring(0, 1), // Convert "Monday" to "M"
        minutes: day.minutes,
    }));

    console.log('🔍 focusMetrics: Processed weeklyData:', weeklyData);
    console.log(
        '🔍 focusMetrics: Total minutes calculated:',
        weeklyData.reduce((sum, day) => sum + day.minutes, 0),
    );

    // Enhanced debug logging for weekly insights
    if (import.meta.env.DEV) {
        console.group('📊 Weekly Insights Debug - focusMetrics computed');
        console.log('Raw userStats from API:', userStats.value);
        console.log('Raw weekly_insight from API:', userStats.value?.weekly_insight);
        console.log('Formatted weekly insights:', formattedWeeklyInsights.value);
        console.log('Processed weekly data for chart:', weeklyData);
        console.log(
            'Individual day minutes:',
            weeklyData.map((d) => `${d.day}: ${d.minutes}min`),
        );
        console.log(
            'Total minutes this week:',
            weeklyData.reduce((sum, day) => sum + day.minutes, 0),
        );
        console.log('Max minutes in week:', Math.max(...weeklyData.map((d) => d.minutes)));
        console.log('Stats loading state:', statsLoading.value);
        console.log('Stats error state:', statsError.value);
        console.log('Should show chart:', weeklyData.reduce((sum, day) => sum + day.minutes, 0) > 0);
        console.groupEnd();
    }

    return {
        dailyStreak: userStats.value?.current_streak || 7,
        longestStreak: userStats.value?.longest_streak || 14,
        weeklyFocusSessions: userStats.value?.total_sessions || 12,
        todayFocusTime: 45, // This could be calculated from today's data
        weeklyGoal: 10, // This could come from user preferences
        weeklyActivity: weeklyData,
        badges: formattedAchievements.value || [],
    };
});

// For safety in template
const unwrappedFocusMetrics = computed(() => focusMetrics.value || {});

// Weekly goal calculation
const totalStudyTime = computed(() => {
    console.log('🔍 totalStudyTime: Computing total study time');
    console.log('🔍 totalStudyTime: formattedWeeklyInsights.value:', formattedWeeklyInsights.value);

    if (!formattedWeeklyInsights.value) {
        console.log('🔍 totalStudyTime: No formatted weekly insights, returning 0');
        return 0;
    }

    const total = formattedWeeklyInsights.value.reduce((total, day) => total + day.minutes, 0);
    console.log('🔍 totalStudyTime: Calculated total:', total, 'minutes');
    return total;
});

// Enhanced chart height calculation for better visibility of small values
const getChartHeight = (minutes: number, maxMinutes: number): number => {
    if (import.meta.env.DEV) {
        console.log(`📏 Chart height calculation: ${minutes} minutes, max: ${maxMinutes} minutes`);
    }

    if (minutes === 0) {
        if (import.meta.env.DEV) console.log('📏 → Height: 0% (no data)');
        return 0;
    }

    // Minimum height for any data (8%)
    const minHeight = 8;

    let calculatedHeight;

    // If maxMinutes is very small (less than 5 minutes), scale differently
    if (maxMinutes < 5) {
        // For very small values, use a different scaling approach
        calculatedHeight = (minutes / maxMinutes) * 80; // Scale to 80% max
    } else {
        // For larger values, use a more reasonable scaling
        calculatedHeight = (minutes / maxMinutes) * 90; // Scale to 90% max instead of /1.5
    }

    const finalHeight = Math.max(calculatedHeight, minHeight);

    if (import.meta.env.DEV) {
        console.log(`📏 → Calculated: ${calculatedHeight.toFixed(1)}%, Final: ${finalHeight.toFixed(1)}%`);
    }

    return finalHeight;
};

// Calculate max minutes for better scaling
const maxWeeklyMinutes = computed(() => {
    if (!unwrappedFocusMetrics.value?.weeklyActivity) return 1;
    const maxMinutes = Math.max(...unwrappedFocusMetrics.value.weeklyActivity.map((day) => day.minutes));
    return maxMinutes > 0 ? maxMinutes : 1;
});

// Activity helper
const isActiveDay = (day: any): boolean => {
    return day.minutes > 0;
};

// Icon mapping function for achievements
const getIconComponent = (iconName: string) => {
    const iconMap: Record<string, any> = {
        Award,
        CheckCircle,
        Zap,
        Brain,
        Activity,
        BookOpen,
    };

    return iconMap[iconName] || Award;
};

// Achievements computed from API data
const badges = computed(() => formattedAchievements.value || []);

// Recent focus sessions state
const recentFocusSessions = ref([]);
const sessionsLoading = ref(false);
const sessionsError = ref(null);

// Fetch recent focus sessions from API
const fetchRecentSessions = async () => {
    try {
        sessionsLoading.value = true;
        sessionsError.value = null;

        const response = await axios.get('/api/v1/sessions?limit=2');

        if (response.data.sessions) {
            recentFocusSessions.value = response.data.sessions
                .slice(0, 2) // Ensure only 2 sessions are displayed
                .map((session) => ({
                    id: session.id,
                    title: session.title || 'Focus Session',
                    duration: calculateSessionDuration(session.started_at, session.ended_at, session.actual_time),
                    pomodoroCount: session.target || 0,
                    tasksCompleted: session.completed_tasks || 0,
                    efficiency: session.completion_percentage || 0,
                    notes: '', // Sessions don't have notes field yet
                    date: new Date(session.started_at),
                    status: session.status,
                    actual_time: session.actual_time || '00:00:00',
                }));
        }
    } catch (error) {
        console.error('Error fetching recent sessions:', error);
        sessionsError.value = 'Failed to load recent sessions';

        // Use fallback data in development
        if (import.meta.env.DEV) {
            recentFocusSessions.value = [
                {
                    id: 1,
                    title: 'Morning Focus',
                    date: new Date(Date.now() - 86400000),
                    duration: 3600,
                    pomodoroCount: 4,
                    tasksCompleted: 3,
                    efficiency: 85,
                    notes: 'Great productive session',
                    status: 'completed',
                    actual_time: '01:00:00',
                },
                {
                    id: 2,
                    title: 'Evening Study',
                    date: new Date(Date.now() - 172800000),
                    duration: 5400,
                    pomodoroCount: 6,
                    tasksCompleted: 5,
                    efficiency: 92,
                    notes: 'Finished important tasks',
                    status: 'completed',
                    actual_time: '01:30:00',
                },
            ];
        }
    } finally {
        sessionsLoading.value = false;
    }
};

// Calculate session duration from API data
const calculateSessionDuration = (startedAt, endedAt, actualTime) => {
    if (actualTime && actualTime !== '00:00:00') {
        // Convert HH:MM:SS to seconds
        const [hours, minutes, seconds] = actualTime.split(':').map(Number);
        return hours * 3600 + minutes * 60 + seconds;
    }

    if (startedAt && endedAt) {
        const start = new Date(startedAt);
        const end = new Date(endedAt);
        return Math.floor((end - start) / 1000);
    }

    return 0;
};

// Format seconds to readable duration
const formatSeconds = (seconds) => {
    if (!seconds) return '0m';

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
};

// Quick Actions navigation functions
const startFocusSession = () => {
    router.visit(route('focus.sessions'));
};

const continueLearning = () => {
    router.visit(route('courses.index'));
};

const viewTasks = () => {
    router.visit(route('focus'));
};

const setGoals = () => {
    router.visit(route('kanban.index'));
};

// Fetch recent sessions on component mount
// Format session date for display
const formatSessionDate = (date) => {
    if (!date) return '';

    const now = new Date();
    const sessionDate = new Date(date);
    const diffTime = Math.abs(now - sessionDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) {
        return 'Yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else {
        return sessionDate.toLocaleDateString();
    }
};

onMounted(() => {
    fetchRecentSessions();
    fetchCourses();
});

// Course data
const coursesInProgress = ref([]);
const coursesLoading = ref(false);
const coursesError = ref(null);
const lastRefresh = ref(null);

// Fetch user courses from API
const fetchCourses = async () => {
    try {
        coursesLoading.value = true;
        coursesError.value = null;

        // Get user UUID from Inertia page props
        const userUuid = user.value?.uuid;

        console.log('🔍 fetchCourses: Starting course fetch with UUID:', userUuid);

        if (!userUuid) {
            throw new Error('User UUID not found. Please ensure user is logged in.');
        }

        const apiUrl = `/api/v1/courses/uuid/${userUuid}`;
        console.log('🔍 fetchCourses: Making request to:', apiUrl);

        const response = await axios.get(apiUrl);

        console.log('🔍 fetchCourses: API response received:', response.data);

        if (response.data.success) {
            coursesInProgress.value = response.data.courses || [];
            lastRefresh.value = new Date();

            console.log('🔍 fetchCourses: Successfully loaded courses:', coursesInProgress.value.length);
            console.log(
                '🔍 fetchCourses: Course titles:',
                coursesInProgress.value.map((c) => c.title),
            );
        } else {
            coursesError.value = response.data.error || 'Failed to load courses';
            console.error('🔍 fetchCourses: API returned error:', coursesError.value);
        }
    } catch (error) {
        console.error('🔍 fetchCourses: Error fetching courses:', error);
        coursesError.value = error.response?.data?.error || error.message || 'Failed to load courses';

        // Use fallback data in development
        if (import.meta.env.DEV) {
            console.warn('🔍 fetchCourses: Using fallback course data for development');
            coursesInProgress.value = [
                {
                    id: 1,
                    title: 'JavaScript Fundamentals',
                    progress: 75,
                    isNew: false,
                    nextLesson: 'Advanced Functions',
                    timeLeft: '2.5 hours',
                    difficulty: 'Intermediate',
                    description: 'Learn the fundamentals of JavaScript programming',
                },
                {
                    id: 2,
                    title: 'Vue.js Complete Guide',
                    progress: 45,
                    isNew: true,
                    nextLesson: 'Components & Props',
                    timeLeft: '4 hours',
                    difficulty: 'Beginner',
                    description: 'Master Vue.js from basics to advanced concepts',
                },
                {
                    id: 3,
                    title: 'Python for Data Science',
                    progress: 90,
                    isNew: false,
                    nextLesson: 'Final Project',
                    timeLeft: '30 minutes',
                    difficulty: 'Advanced',
                    description: 'Complete guide to Python for data analysis',
                },
                {
                    id: 4,
                    title: 'React Development',
                    progress: 100,
                    isNew: false,
                    nextLesson: 'Course Complete',
                    timeLeft: 'Completed',
                    difficulty: 'Advanced',
                    description: 'Build modern web applications with React',
                },
            ];
            lastRefresh.value = new Date();
        }
    } finally {
        coursesLoading.value = false;
        console.log('🔍 fetchCourses: Fetch completed. Final courses:', coursesInProgress.value.length);
    }
};

// Course statistics computed properties
const completedCoursesCount = computed(() => {
    return coursesInProgress.value.filter((course) => course.progress >= 100).length;
});

const inProgressCoursesCount = computed(() => {
    return coursesInProgress.value.filter((course) => course.progress > 0 && course.progress < 100).length;
});

const averageProgress = computed(() => {
    if (coursesInProgress.value.length === 0) return 0;
    const totalProgress = coursesInProgress.value.reduce((sum, course) => sum + (course.progress || 0), 0);
    return Math.round(totalProgress / coursesInProgress.value.length);
});

// Utility functions
const formatMinutes = (minutes: number): string => {
    if (!minutes || minutes < 0) return '0 min';
    if (minutes < 60) return `${minutes} min`;

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (remainingMinutes === 0) return `${hours} ${hours === 1 ? 'hour' : 'hours'}`;
    return `${hours}h ${remainingMinutes}m`;
};

const formatTimeOfDay = () => {
    const now = new Date();
    const hour = now.getHours();

    if (hour < 12) return 'Morning';
    if (hour < 17) return 'Afternoon';
    return 'Evening';
};

const todayFormatted = computed(() => {
    const now = new Date();
    return now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const dailyGreeting = computed(() => {
    const now = new Date();
    const hour = now.getHours();
    const quotes = [
        "Believe you can and you're halfway there.",
        'It does not matter how slowly you go as long as you do not stop.',
        'Success is not final, failure is not fatal: It is the courage to continue that counts.',
        "Don't watch the clock; do what it does. Keep going.",
        "You miss 100% of the shots you don't take.",
    ];
    const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];

    if (hour < 12) return `Good morning! ${randomQuote}`;
    if (hour < 17) return `Good afternoon! ${randomQuote}`;
    return `Good evening! ${randomQuote}`;
});

const getCourseColor = (id: number) => {
    const colors = ['#3498db', '#f1c40f', '#2ecc71', '#e74c3c', '#9b59b6', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7'];
    return colors[id % colors.length];
};

const getCourseInitials = (title: string) => {
    if (!title || title.trim() === '') return '??';
    const words = title
        .trim()
        .split(' ')
        .filter((word) => word.length > 0);
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }
    return words
        .slice(0, 2)
        .map((word) => word[0].toUpperCase())
        .join('');
};
</script>

<style>
.animate-gradient-circle {
    animation: gradient-rotate 8s linear infinite;
}

.bg-grid-white {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.05)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}

@keyframes gradient-rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.card-hover-effect {
    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
}

.card-hover-effect:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.1);
}

.dark .card-hover-effect:hover {
    box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.3);
}

.bg-grid-white {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.03)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-card-in {
    animation: fade-in 0.5s ease-out;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom animations */
@keyframes pulse-slow {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse-slow {
    animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
