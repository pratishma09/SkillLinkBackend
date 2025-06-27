<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            ['title' => 'E-commerce Website', 'description' => 'Build a Laravel + Vue e-commerce web platform.'],
            ['title' => 'Mobile Banking App', 'description' => 'Develop an Android/iOS app for banking services.'],
            ['title' => 'Online Learning System', 'description' => 'Create an LMS with student tracking features.'],
            ['title' => 'Job Portal', 'description' => 'Develop a job board with search and filter capabilities.'],
            ['title' => 'Travel Booking App', 'description' => 'A cross-platform app for booking travel packages.'],
            ['title' => 'Food Delivery Platform', 'description' => 'Build backend and mobile frontend for food delivery.'],
            ['title' => 'Fitness Tracker App', 'description' => 'An app to log exercises and meal tracking.'],
            ['title' => 'Crypto Wallet App', 'description' => 'Build a secure crypto wallet with React Native.'],
            ['title' => 'Project Management Tool', 'description' => 'Web app to manage teams, tasks, and deadlines.'],
            ['title' => 'Portfolio Builder', 'description' => 'Tool for users to build personal online portfolios.'],
            ['title' => 'Real Estate App', 'description' => 'Search, list, and book properties using maps.'],
            ['title' => 'Healthcare Management System', 'description' => 'Web portal for hospitals and appointments.'],
            ['title' => 'Event Booking Platform', 'description' => 'Manage and reserve tickets for events.'],
            ['title' => 'Social Networking App', 'description' => 'Minimal social app with friend requests and chat.'],
            ['title' => 'School Management System', 'description' => 'App for school admin, teachers, and parents.'],
            ['title' => 'Online Grocery Store', 'description' => 'App to shop and manage groceries with delivery.'],
            ['title' => 'Freelance Marketplace', 'description' => 'Connect freelancers and employers.'],
            ['title' => 'Task Scheduler', 'description' => 'To-do list app with time-based reminders.'],
            ['title' => 'News Aggregator App', 'description' => 'Pulls and curates news from multiple sources.'],
            ['title' => 'Bug Tracker Tool', 'description' => 'Track bugs and issue resolution across teams.'],
        ];

        $types = ['internship', 'full-time', 'part-time', 'contract'];
        $statuses = ['active', 'closed', 'draft'];

        foreach ($projects as $project) {
            $createProject = new Project();
            $createProject->title = $project['title'];
            $createProject->description = $project['description'];
            $createProject->posted_by = 3;
            $createProject->type_of_project = $types[array_rand($types)];
            $createProject->status = $statuses[array_rand($statuses)];
            $createProject->requirements = ['Basic Laravel knowledge', 'Teamwork', 'REST APIs'];
            $createProject->project_category_id = rand(1, 10);
            $createProject->skills_required = ['Laravel', 'Vue.js'];
            $createProject->location = 'Remote';
            $createProject->salary = rand(10000, 80000);
            $createProject->deadline = now()->addDays(rand(7, 30));
            $createProject->save();
        }
    }
}
