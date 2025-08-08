<template>
    <AppLayout>
        <div
            class="min-h-screen transition-colors duration-300"
            :class="{
                'via-pink-25 bg-gradient-to-br from-rose-50 to-orange-50': !darkMode,
                'bg-gray-900 text-gray-100': darkMode,
            }"
            :style="!darkMode ? 'background: hsl(0 100% 99%);' : ''"
        >
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="mb-4 flex justify-end">
                    <button
                        @click="toggleDarkMode"
                        class="rounded-full p-2 transition-colors duration-300"
                        :class="darkMode ? 'bg-gray-700 text-yellow-300 hover:bg-gray-600' : 'bg-orange-100 text-orange-600 hover:bg-orange-200'"
                    >
                        <SunIcon v-if="darkMode" class="h-5 w-5" />
                        <MoonIcon v-else class="h-5 w-5" />
                    </button>
                </div>

                <!-- Stats Cards Grid -->
                <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Study Time Card -->
                    <Card
                        class="card-hover-effect group relative min-h-[120px] overflow-hidden border-border/50 shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:text-white dark:hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-medium">Total Study Time</CardTitle>
                                <Clock class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-foreground">
                                        <span v-if="statsLoading">Loading...</span>
                                        <span v-else-if="statsError">Error</span>
                                        <span v-else>{{ stats.totalStudyTime }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">Time focused today</p>
                                </div>
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-400"
                                >
                                    <Clock class="h-6 w-6" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Current Streak Card -->
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
                                        <span v-else-if="statsError">Error</span>
                                        <span v-else>{{ stats.streak }} days</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span v-if="!statsLoading && !statsError">{{ stats.streakPercentage }}% of your goal</span>
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

                    <!-- Average Efficiency Card -->
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
                                        <span v-else-if="statsError">Error</span>
                                        <span v-else>{{ stats.efficiency }}%</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span v-if="!statsLoading && !statsError" :class="stats.efficiencyChange.colorClass">{{
                                            stats.efficiencyChange.text
                                        }}</span>
                                        from last week
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

                    <!-- Tasks Completed Card -->
                    <Card
                        class="card-hover-effect group relative min-h-[120px] overflow-hidden border-border/50 shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:text-white dark:hover:border-primary/30"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        ></div>
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-medium">Tasks Completed</CardTitle>
                                <CheckCircle class="h-4 w-4 text-muted-foreground" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="text-2xl font-bold text-foreground">
                                        <span v-if="statsLoading">Loading...</span>
                                        <span v-else-if="statsError">Error</span>
                                        <span v-else>{{ stats.tasksCompleted }}</span>
                                    </div>
                                    <p class="text-xs text-muted-foreground dark:text-white/80">
                                        <span v-if="!statsLoading && !statsError" :class="stats.tasksCompletedChange.colorClass">{{
                                            stats.tasksCompletedChange.text
                                        }}</span>
                                        from last week
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

                <div
                    class="overflow-hidden rounded-2xl shadow-xl backdrop-blur-sm"
                    :class="{
                        'border border-rose-100 bg-white/80': !darkMode,
                        'border border-gray-700 bg-gray-800/80': darkMode,
                    }"
                >
                    <div class="border-b" :class="darkMode ? 'border-gray-700' : 'border-rose-100'">
                        <nav class="flex space-x-8 px-8">
                            <button
                                v-for="tab in ['overview', 'payments', 'users', 'contacts']"
                                :key="tab"
                                @click="activeTab = tab"
                                :class="[
                                    'border-b-2 px-1 py-4 text-sm font-medium capitalize transition-colors',
                                    activeTab === tab
                                        ? 'border-orange-500 text-orange-600'
                                        : darkMode
                                          ? 'border-transparent text-gray-400 hover:border-gray-200 hover:text-gray-200'
                                          : 'border-transparent text-slate-500 hover:border-rose-200 hover:text-slate-700',
                                ]"
                            >
                                {{ tab }}
                            </button>
                        </nav>
                    </div>

                    <div class="p-8">
                        <div v-if="activeTab === 'overview'" class="space-y-6">
                            <div class="flex flex-col items-start justify-between space-y-4 sm:flex-row sm:items-center sm:space-y-0">
                                <div class="relative">
                                    <SearchIcon
                                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 transform"
                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Search testimonials..."
                                        v-model="searchTermTestimonials"
                                        class="rounded-lg border py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="
                                            darkMode ? 'border-gray-600 bg-gray-700 text-gray-200 placeholder-gray-400' : 'border-rose-200 bg-white'
                                        "
                                    />
                                </div>
                                <button
                                    @click="generateAndCopyTestimonialLink"
                                    class="flex items-center rounded-lg bg-orange-500 px-4 py-2 text-white shadow-sm transition-colors hover:bg-orange-600"
                                >
                                    <PlusIcon class="mr-2 h-4 w-4" />
                                    Add Testimonial
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b" :class="darkMode ? 'border-gray-700' : 'border-rose-200'">
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Author
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Photo
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Position
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Feedback
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Rating
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Approved
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Date
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="testimonial in testimonials.data"
                                            :key="testimonial.id"
                                            class="border-b transition-colors"
                                            :class="darkMode ? 'border-gray-800 hover:bg-gray-700' : 'hover:bg-rose-25 border-rose-100'"
                                        >
                                            <td class="px-4 py-4 font-medium" :class="darkMode ? 'text-gray-200' : 'text-slate-800'">
                                                {{ testimonial.name || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <img
                                                    v-if="testimonial.photo"
                                                    :src="testimonial.photo"
                                                    alt="Author Photo"
                                                    class="h-10 w-10 rounded-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs text-gray-500"
                                                >
                                                    N/A
                                                </div>
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ testimonial.position || 'N/A' }}
                                            </td>
                                            <td
                                                class="max-w-xs overflow-hidden text-ellipsis whitespace-nowrap px-4 py-4 text-sm"
                                                :class="darkMode ? 'text-gray-400' : 'text-slate-600'"
                                                :title="testimonial.feedback"
                                            >
                                                {{ testimonial.feedback }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ testimonial.rating || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    :class="
                                                        testimonial.approved
                                                            ? 'inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600'
                                                            : 'inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-600'
                                                    "
                                                >
                                                    <component :is="testimonial.approved ? CheckCircleIcon : XCircleIcon" class="mr-1 h-3 w-3" />
                                                    {{ testimonial.approved ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ formatDate(testimonial.created_at) }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <button
                                                        class="p-1 transition-colors hover:text-orange-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <EyeIcon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-green-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <Edit3Icon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-red-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <Trash2Icon class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="testimonials.data.length === 0">
                                            <td :colspan="8" class="px-4 py-4 text-center text-gray-500">No testimonials found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination :links="testimonials.links" :dark-mode="darkMode" />
                        </div>

                        <div v-if="activeTab === 'payments'" class="space-y-6">
                            <div class="flex flex-col items-start justify-between space-y-4 sm:flex-row sm:items-center sm:space-y-0">
                                <div class="relative">
                                    <SearchIcon
                                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 transform"
                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Search payments..."
                                        v-model="searchTermPayments"
                                        class="rounded-lg border py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="
                                            darkMode ? 'border-gray-600 bg-gray-700 text-gray-200 placeholder-gray-400' : 'border-rose-200 bg-white'
                                        "
                                    />
                                </div>
                                <button
                                    class="flex items-center rounded-lg bg-orange-500 px-4 py-2 text-white shadow-sm transition-colors hover:bg-orange-600"
                                >
                                    <PlusIcon class="mr-2 h-4 w-4" />
                                    Add Payment Method
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b" :class="darkMode ? 'border-gray-700' : 'border-rose-200'">
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Invoice
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Date
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Amount
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Method
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Status
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="payment in payments.data"
                                            :key="payment.id"
                                            class="border-b transition-colors"
                                            :class="darkMode ? 'border-gray-800 hover:bg-gray-700' : 'hover:bg-rose-25 border-rose-100'"
                                        >
                                            <td class="px-4 py-4 font-medium" :class="darkMode ? 'text-gray-200' : 'text-slate-800'">
                                                {{ payment.invoice }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ formatDate(payment.date) }}
                                            </td>
                                            <td class="px-4 py-4 font-medium" :class="darkMode ? 'text-gray-200' : 'text-slate-800'">
                                                ${{ payment.amount }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ payment.method }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <span :class="getStatusClass(payment.status, true)">
                                                    <component :is="getStatusIcon(payment.status)" class="mr-1 h-3 w-3" />
                                                    {{ payment.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <button
                                                        class="p-1 transition-colors hover:text-orange-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <EyeIcon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-green-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <DownloadIcon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-slate-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <MoreVerticalIcon class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="payments.data.length === 0">
                                            <td :colspan="6" class="px-4 py-4 text-center text-gray-500">No payments found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination :links="payments.links" :dark-mode="darkMode" />
                        </div>

                        <div v-if="activeTab === 'users'" class="space-y-6">
                            <div class="flex flex-col items-start justify-between space-y-4 sm:flex-row sm:items-center sm:space-y-0">
                                <div class="relative">
                                    <SearchIcon
                                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 transform"
                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Search users..."
                                        v-model="searchTermUsers"
                                        class="rounded-lg border py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="
                                            darkMode ? 'border-gray-600 bg-gray-700 text-gray-200 placeholder-gray-400' : 'border-rose-200 bg-white'
                                        "
                                    />
                                </div>
                                <button
                                    class="flex items-center rounded-lg bg-orange-500 px-4 py-2 text-white shadow-sm transition-colors hover:bg-orange-600"
                                >
                                    <PlusIcon class="mr-2 h-4 w-4" />
                                    Add User
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b" :class="darkMode ? 'border-gray-700' : 'border-rose-200'">
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                User
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Email Verified
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Member Since
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Last Updated
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="userData in users.data"
                                            :key="userData.id"
                                            class="border-b transition-colors"
                                            :class="darkMode ? 'border-gray-800 hover:bg-gray-700' : 'hover:bg-rose-25 border-rose-100'"
                                        >
                                            <td class="px-4 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-orange-400 to-pink-500 font-semibold text-white shadow-sm"
                                                    >
                                                        {{ getInitials(userData.name) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-medium" :class="darkMode ? 'text-gray-200' : 'text-slate-800'">
                                                            {{ userData.name }}
                                                        </div>
                                                        <div class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-slate-500'">
                                                            {{ userData.email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span
                                                    :class="
                                                        userData.email_verified_at
                                                            ? 'inline-flex items-center rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600'
                                                            : 'inline-flex items-center rounded-full bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-600'
                                                    "
                                                >
                                                    <component
                                                        :is="userData.email_verified_at ? CheckCircleIcon : AlertCircleIcon"
                                                        class="mr-1 h-3 w-3"
                                                    />
                                                    {{ userData.email_verified_at ? 'Verified' : 'Unverified' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ formatDate(userData.created_at) }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ formatDate(userData.updated_at) }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <button
                                                        class="p-1 transition-colors hover:text-orange-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <EyeIcon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-green-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <Edit3Icon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-red-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <Trash2Icon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-slate-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <MoreVerticalIcon class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="users.data.length === 0">
                                            <td :colspan="5" class="px-4 py-4 text-center text-gray-500">No users found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination :links="users.links" :dark-mode="darkMode" />
                        </div>

                        <div v-if="activeTab === 'contacts'" class="space-y-6">
                            <div class="flex flex-col items-start justify-between space-y-4 sm:flex-row sm:items-center sm:space-y-0">
                                <div class="relative">
                                    <SearchIcon
                                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 transform"
                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                    />
                                    <input
                                        type="text"
                                        placeholder="Search contacts..."
                                        v-model="searchTermContacts"
                                        class="rounded-lg border py-2 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-orange-500"
                                        :class="
                                            darkMode ? 'border-gray-600 bg-gray-700 text-gray-200 placeholder-gray-400' : 'border-rose-200 bg-white'
                                        "
                                    />
                                </div>
                                <button
                                    class="flex items-center rounded-lg bg-orange-500 px-4 py-2 text-white shadow-sm transition-colors hover:bg-orange-600"
                                >
                                    <PlusIcon class="mr-2 h-4 w-4" />
                                    Add Contact
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b" :class="darkMode ? 'border-gray-700' : 'border-rose-200'">
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Name
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Email
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Phone
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Company
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Country
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Message
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Received At
                                            </th>
                                            <th class="px-4 py-3 text-left font-medium" :class="darkMode ? 'text-gray-300' : 'text-slate-600'">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="contact in contacts.data"
                                            :key="contact.id"
                                            class="border-b transition-colors"
                                            :class="darkMode ? 'border-gray-800 hover:bg-gray-700' : 'hover:bg-rose-25 border-rose-100'"
                                        >
                                            <td class="px-4 py-4 font-medium" :class="darkMode ? 'text-gray-200' : 'text-slate-800'">
                                                {{ contact.name || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ contact.email || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ contact.phone || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ contact.company || 'N/A' }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ contact.country || 'N/A' }}
                                            </td>
                                            <td
                                                class="max-w-xs overflow-hidden text-ellipsis whitespace-nowrap px-4 py-4 text-sm"
                                                :class="darkMode ? 'text-gray-400' : 'text-slate-600'"
                                                :title="contact.message"
                                            >
                                                {{ contact.message }}
                                            </td>
                                            <td class="px-4 py-4" :class="darkMode ? 'text-gray-400' : 'text-slate-600'">
                                                {{ formatDate(contact.created_at) }}
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <button
                                                        class="p-1 transition-colors hover:text-orange-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <EyeIcon class="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        class="p-1 transition-colors hover:text-red-600"
                                                        :class="darkMode ? 'text-gray-400' : 'text-slate-400'"
                                                    >
                                                        <Trash2Icon class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="contacts.data.length === 0">
                                            <td :colspan="8" class="px-4 py-4 text-center text-gray-500">No contacts found.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination :links="contacts.links" :dark-mode="darkMode" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3'; // Import router and Link from Inertia
import {
    Activity,
    AlertCircle as AlertCircleIcon,
    CheckCircle,
    CheckCircle as CheckCircleIcon,
    Clock,
    Download as DownloadIcon,
    Edit3 as Edit3Icon,
    Eye as EyeIcon,
    Flame,
    Moon as MoonIcon,
    MoreVertical as MoreVerticalIcon,
    Plus as PlusIcon,
    Search as SearchIcon,
    Sun as SunIcon,
    Trash2 as Trash2Icon,
    XCircle as XCircleIcon,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue'; // Add 'watch' and 'provide'

// Import Card components
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import CardHeader from '@/components/ui/card/CardHeader.vue';
import CardTitle from '@/components/ui/card/CardTitle.vue';

// Import stats composable and service
import { useGetStatsComposable } from '@/composables/useGetStatsComposable';
import { statsApiService } from '@/services/statsApiService';

// Import the new Pagination component
import Pagination from '@/components/Pagination.vue';

// Props - Now expecting paginated objects from Laravel
const props = defineProps({
    // Users will be paginated
    contacts: {
        type: Object, // Expect an object with 'data', 'links', etc.
        required: true,
    },
    // Users will be paginated
    users: {
        type: Object, // Expect an object with 'data', 'links', etc.
        required: true,
    },
    // Payments will be paginated
    payments: {
        type: Object, // Expect an object with 'data', 'links', etc.
        required: true,
    },
    // Testimonials will be paginated
    testimonials: {
        type: Object, // Expect an object with 'data', 'links', etc.
        required: true,
    },
    // We'll also need the initial search terms if they were passed
    initialSearchTerms: {
        type: Object,
        default: () => ({
            testimonials: '',
            payments: '',
            users: '',
        }),
    },
    // The active tab might also be passed via Inertia if you want to preserve it on page loads
    initialActiveTab: {
        type: String,
        default: 'overview',
    },
});

// Reactive data
const activeTab = ref(props.initialActiveTab);

// Reactive search terms for each table. These will drive Inertia requests.
const searchTermTestimonials = ref(props.initialSearchTerms.testimonials);
const searchTermPayments = ref(props.initialSearchTerms.payments); // Renamed from general 'searchTerm'
const searchTermUsers = ref(props.initialSearchTerms.users); // Renamed from general 'searchTerm'
const searchTermContacts = ref('');

const darkMode = ref(false);

// Toggle dark mode (unchanged)
const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
};

// Initialize stats composable
const {
    stats: userStats,
    isLoading: statsLoading,
    error: statsError,
    formattedStudyTime,
    formattedEfficiencyChange,
    formattedTasksChange,
} = useGetStatsComposable();

// Computed property for stats data with fallbacks
const stats = computed(() => ({
    totalStudyTime: formattedStudyTime.value,
    streak: userStats.value?.current_streak || 0,
    streakPercentage: userStats.value ? statsApiService.calculateStreakPercentage(userStats.value.current_streak) : 0,
    efficiency: userStats.value?.average_efficiency || 0,
    efficiencyChange: formattedEfficiencyChange.value,
    tasksCompleted: userStats.value?.completed_sessions || 0,
    tasksCompletedChange: formattedTasksChange.value,
}));

// --- Inertia Request Logic ---
// When a search term changes, trigger a new Inertia request with debounce

// Watch for changes in testimonial search term
watch(searchTermTestimonials, (value) => {
    // Debounce the search to avoid too many requests
    debouncedSearch('testimonials', value);
});

// Watch for changes in payment search term
watch(searchTermPayments, (value) => {
    debouncedSearch('payments', value);
});

// Watch for changes in user search term
watch(searchTermUsers, (value) => {
    debouncedSearch('users', value);
});

// Watch for activeTab changes to potentially reset search terms or adjust URL
watch(activeTab, () => {
    // You might want to push a new state to history or trigger a backend request
    // if changing tabs should also reload data. For now, we'll keep it simple
    // and assume the backend provides all necessary data for all tabs on initial load.
    // If not, you'd do:
    // router.get(route('your.page.route', { tab: newTab }), {}, { preserveState: true, replace: true });
});

let debounceTimeout: number | null = null;

const debouncedSearch = (_table: string, _term: string) => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
    debounceTimeout = setTimeout(() => {
        // Construct the URL based on the active tab and current search terms
        // You might have a single route that handles all tabs, or separate routes.
        // For simplicity, we assume one route for this page that can take query params.
        router.get(
            route('your.page.route'),
            {
                tab: activeTab.value, // Pass the active tab
                search_testimonials: searchTermTestimonials.value,
                search_payments: searchTermPayments.value,
                search_users: searchTermUsers.value,
            },
            {
                preserveState: true, // Keep component state (e.g., active tab)
                replace: true, // Replace history entry instead of pushing a new one
            },
        );
    }, 300); // 300ms debounce
};

// Methods (unchanged, as they are helpers)
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

const getInitials = (name: string) => {
    if (!name) return '';
    return name
        .split(' ')
        .map((word: string) => word.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const getStatusClass = (status: string, withIcon = false) => {
    const baseClasses = withIcon
        ? 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium'
        : 'px-4 py-2 rounded-full text-sm font-medium';

    switch (
        (status || '').toLowerCase() // Handle potentially null status
    ) {
        case 'paid':
        case 'active':
            return `${baseClasses} text-green-600 bg-green-50`;
        case 'failed':
        case 'inactive':
            return `${baseClasses} text-red-600 bg-red-50`;
        case 'pending':
        case 'trial':
            return `${baseClasses} text-yellow-600 bg-yellow-50`;
        default:
            return `${baseClasses} text-gray-600 bg-gray-50`;
    }
};

const getStatusIcon = (status: string) => {
    switch (
        (status || '').toLowerCase() // Handle potentially null status
    ) {
        case 'paid':
        case 'active':
            return CheckCircleIcon;
        case 'failed':
        case 'inactive':
            return XCircleIcon;
        case 'pending':
        case 'trial':
            return AlertCircleIcon;
        default:
            return AlertCircleIcon;
    }
};

// Function to generate and copy the testimonial form link
const generateAndCopyTestimonialLink = async () => {
    const link = 'http://127.0.0.1:8002/testimonials/form';
    try {
        await navigator.clipboard.writeText(link);
        alert(`Link copied to clipboard: ${link}`);
    } catch (err) {
        console.error('Failed to copy link: ', err);
        alert(`Failed to copy link. Please manually copy: ${link}`);
    }
};
</script>
