<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        $categories = $this->seedCategories();
        $tags = $this->seedTags();
        $this->seedApplications($categories, $tags);
    }

    private function seedCategories(): array
    {
        $categories = [
            ['name' => 'Applications', 'slug' => 'applications', 'description' => 'General purpose applications', 'icon' => 'app', 'sort_order' => 1],
            ['name' => 'Games', 'slug' => 'games', 'description' => 'Games and entertainment', 'icon' => 'gamepad', 'sort_order' => 2],
            ['name' => 'Media & Design', 'slug' => 'media-design', 'description' => 'Creative and design tools', 'icon' => 'palette', 'sort_order' => 3],
            ['name' => 'System Utilities', 'slug' => 'system-utilities', 'description' => 'System tools and utilities', 'icon' => 'wrench', 'sort_order' => 4],
            ['name' => 'Productivity & Business', 'slug' => 'productivity-business', 'description' => 'Business and productivity software', 'icon' => 'briefcase', 'sort_order' => 5],
            ['name' => 'Developer Tools', 'slug' => 'developer-tools', 'description' => 'Tools for developers', 'icon' => 'code', 'sort_order' => 6],
            ['name' => 'Internet', 'slug' => 'internet', 'description' => 'Internet and networking tools', 'icon' => 'globe', 'sort_order' => 7],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Educational software', 'icon' => 'book', 'sort_order' => 8],
            ['name' => 'Security', 'slug' => 'security', 'description' => 'Security and privacy tools', 'icon' => 'shield', 'sort_order' => 9],
            ['name' => 'Graphics', 'slug' => 'graphics', 'description' => 'Graphics and image editing', 'icon' => 'image', 'sort_order' => 10],
            ['name' => 'Audio', 'slug' => 'audio', 'description' => 'Audio editing and production', 'icon' => 'music', 'sort_order' => 11],
            ['name' => 'Video', 'slug' => 'video', 'description' => 'Video editing and production', 'icon' => 'video', 'sort_order' => 12],
        ];

        $result = [];
        foreach ($categories as $cat) {
            $result[$cat['slug']] = Category::create($cat);
        }

        return $result;
    }

    private function seedTags(): array
    {
        $tags = ['Free', 'Paid', 'Open Source', 'Mac', 'Windows', 'iOS', 'Android', 'Pro', 'Lite', 'New', 'Updated', 'Popular'];
        $result = [];
        foreach ($tags as $tag) {
            $result[$tag] = Tag::create(['name' => $tag, 'slug' => Str::slug($tag)]);
        }

        return $result;
    }

    private function seedApplications(array $categories, array $tags): void
    {
        $apps = [
            [
                'name' => 'Adobe Photoshop',
                'category' => 'media-design',
                'short_description' => 'Professional image editing software',
                'description' => 'Adobe Photoshop is the industry-standard software for digital image editing, photo retouching, and graphic design. Used by photographers, designers, and artists worldwide.',
                'version' => '26.3.5',
                'size' => '2.8 GB',
                'developer' => 'Adobe Inc.',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => true,
                'popular' => true,
                'downloads' => 33300,
                'rating' => 4.8,
                'reviews_count' => 1250,
                'released_at' => '2026-09-15',
                'tags' => ['Paid', 'Pro', 'Popular'],
            ],
            [
                'name' => 'Farming Simulator 25',
                'category' => 'games',
                'short_description' => 'Agricultural simulation game',
                'description' => 'Farming Simulator 25 puts you in the role of a modern farmer. Cultivate fields, raise livestock, and expand your farm in open-world environments.',
                'version' => '1.23.1',
                'size' => '15.2 GB',
                'developer' => 'GIANTS Software',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => true,
                'popular' => true,
                'downloads' => 1500,
                'rating' => 4.5,
                'reviews_count' => 890,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Popular'],
            ],
            [
                'name' => 'CleanMyMac',
                'category' => 'system-utilities',
                'short_description' => 'Mac optimization and cleaning tool',
                'description' => 'CleanMyMac X is the leading utility for keeping your Mac clean, fast, and protected. Remove malware, optimize performance, and free up disk space.',
                'version' => '5.7.0',
                'size' => '89 MB',
                'developer' => 'MacPaw',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => true,
                'popular' => true,
                'downloads' => 27800,
                'rating' => 4.4,
                'reviews_count' => 483,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac', 'Popular'],
            ],
            [
                'name' => 'Microsoft Office',
                'category' => 'productivity-business',
                'short_description' => 'Productivity suite with Word, Excel, PowerPoint',
                'description' => 'Microsoft Office is the essential productivity suite including Word, Excel, PowerPoint, and Outlook for home and business use.',
                'version' => '16.91',
                'size' => '4.1 GB',
                'developer' => 'Microsoft',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => true,
                'popular' => true,
                'downloads' => 20000,
                'rating' => 4.6,
                'reviews_count' => 2100,
                'released_at' => '2026-09-20',
                'tags' => ['Paid', 'Pro', 'Popular'],
            ],
            [
                'name' => 'Parallels Desktop',
                'category' => 'system-utilities',
                'short_description' => 'Run Windows on Mac',
                'description' => 'Parallels Desktop allows you to run Windows and Linux applications on your Mac without rebooting. Seamless integration between operating systems.',
                'version' => '20.2',
                'size' => '1.2 GB',
                'developer' => 'Parallels International',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => true,
                'popular' => false,
                'downloads' => 34200,
                'rating' => 4.3,
                'reviews_count' => 670,
                'released_at' => '2026-09-10',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => 'SimpleMind Pro',
                'category' => 'productivity-business',
                'short_description' => 'Mind mapping and brainstorming tool',
                'description' => 'SimpleMind Pro is a professional mind mapping tool for organizing ideas, planning projects, and brainstorming with visual diagrams.',
                'version' => '2.10.2',
                'size' => '45 MB',
                'developer' => 'ModelMaker Tools',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => false,
                'downloads' => 175,
                'rating' => 4.2,
                'reviews_count' => 120,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Pro'],
            ],
            [
                'name' => 'NetWorker Pro',
                'category' => 'system-utilities',
                'short_description' => 'Network monitoring and analysis',
                'description' => 'NetWorker Pro provides real-time network monitoring, bandwidth analysis, and connection diagnostics for your Mac.',
                'version' => '11.3.0',
                'size' => '12 MB',
                'developer' => 'NPP Development',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => false,
                'popular' => false,
                'downloads' => 174,
                'rating' => 4.1,
                'reviews_count' => 89,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => 'Rectangle Pro',
                'category' => 'productivity-business',
                'short_description' => 'Window management for Mac',
                'description' => 'Rectangle Pro is the best window management app for macOS. Snap windows, resize with keyboard shortcuts, and organize your workspace efficiently.',
                'version' => '3.92',
                'size' => '8 MB',
                'developer' => 'Ryan Hanson',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => false,
                'popular' => false,
                'downloads' => 853,
                'rating' => 4.7,
                'reviews_count' => 340,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => 'A Better Finder Attributes',
                'category' => 'system-utilities',
                'short_description' => 'File and folder attribute editor',
                'description' => 'A Better Finder Attributes is a powerful utility for editing file and folder attributes, timestamps, permissions, and metadata on macOS.',
                'version' => '7.50',
                'size' => '18 MB',
                'developer' => 'Publicspace.net',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => false,
                'popular' => false,
                'downloads' => 289,
                'rating' => 4.0,
                'reviews_count' => 67,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => 'Deckset',
                'category' => 'developer-tools',
                'short_description' => 'Markdown presentation creator',
                'description' => 'Deckset turns your Markdown files into beautiful presentations. Write your slides in your favorite editor and present with style.',
                'version' => '2.1.0',
                'size' => '22 MB',
                'developer' => 'Deckset GmbH',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => false,
                'popular' => false,
                'downloads' => 22,
                'rating' => 4.3,
                'reviews_count' => 45,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => 'PowerPhotos',
                'category' => 'graphics',
                'short_description' => 'Photo library manager for Mac',
                'description' => 'PowerPhotos is a professional photo library manager for macOS. Manage multiple Photos libraries, merge libraries, and find duplicates.',
                'version' => '3.4.8',
                'size' => '28 MB',
                'developer' => 'Fat Cat Software',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => false,
                'popular' => false,
                'downloads' => 554,
                'rating' => 4.5,
                'reviews_count' => 156,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => 'RightFont',
                'category' => 'graphics',
                'short_description' => 'Font management for designers',
                'description' => 'RightFont is a professional font management application for macOS. Organize, preview, and activate fonts with ease.',
                'version' => '10.2.1',
                'size' => '35 MB',
                'developer' => 'Markly Team',
                'license' => 'Commercial',
                'platform' => 'macOS',
                'featured' => false,
                'popular' => false,
                'downloads' => 624,
                'rating' => 4.4,
                'reviews_count' => 98,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Mac'],
            ],
            [
                'name' => '4K YouTube to MP3 Pro',
                'category' => 'audio',
                'short_description' => 'Extract audio from YouTube videos',
                'description' => '4K YouTube to MP3 Pro allows you to extract audio from YouTube videos and save it as high-quality MP3 files.',
                'version' => '26.3.5',
                'size' => '52 MB',
                'developer' => 'OpenMedia LLC',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => false,
                'downloads' => 1500,
                'rating' => 4.2,
                'reviews_count' => 234,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Pro'],
            ],
            [
                'name' => '4K Video Downloader Plus Pro',
                'category' => 'video',
                'short_description' => 'Download videos from YouTube and more',
                'description' => '4K Video Downloader Plus Pro lets you download videos, playlists, and channels from YouTube, Vimeo, and other platforms in high quality.',
                'version' => '26.3.5',
                'size' => '68 MB',
                'developer' => 'OpenMedia LLC',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => false,
                'downloads' => 3900,
                'rating' => 4.3,
                'reviews_count' => 456,
                'released_at' => '2026-10-01',
                'tags' => ['Paid', 'Pro'],
            ],
            [
                'name' => 'Visual Studio Code',
                'category' => 'developer-tools',
                'short_description' => 'Code editor with debugging and Git',
                'description' => 'Visual Studio Code is a lightweight but powerful source code editor with built-in support for JavaScript, TypeScript, Node.js, and rich extensions for other languages.',
                'version' => '1.95.0',
                'size' => '340 MB',
                'developer' => 'Microsoft',
                'license' => 'Free',
                'platform' => 'macOS, Windows, Linux',
                'featured' => true,
                'popular' => true,
                'downloads' => 45000,
                'rating' => 4.9,
                'reviews_count' => 3200,
                'released_at' => '2026-09-25',
                'tags' => ['Free', 'Open Source', 'Popular'],
            ],
            [
                'name' => 'Figma',
                'category' => 'media-design',
                'short_description' => 'Collaborative interface design tool',
                'description' => 'Figma is a collaborative web application for interface design with real-time collaboration features. Design, prototype, and gather feedback all in one place.',
                'version' => '124.5.5',
                'size' => '180 MB',
                'developer' => 'Figma Inc.',
                'license' => 'Freemium',
                'platform' => 'macOS, Windows',
                'featured' => true,
                'popular' => true,
                'downloads' => 38000,
                'rating' => 4.8,
                'reviews_count' => 2800,
                'released_at' => '2026-09-28',
                'tags' => ['Free', 'Popular'],
            ],
            [
                'name' => 'Notion',
                'category' => 'productivity-business',
                'short_description' => 'All-in-one workspace',
                'description' => 'Notion is an all-in-one workspace for notes, tasks, wikis, and databases. Write, plan, collaborate, and get organized.',
                'version' => '3.1.2',
                'size' => '220 MB',
                'developer' => 'Notion Labs',
                'license' => 'Freemium',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => true,
                'downloads' => 52000,
                'rating' => 4.7,
                'reviews_count' => 4100,
                'released_at' => '2026-09-22',
                'tags' => ['Free', 'Popular'],
            ],
            [
                'name' => 'Spotify',
                'category' => 'audio',
                'short_description' => 'Music streaming service',
                'description' => 'Spotify is a digital music service that gives you access to millions of songs, podcasts, and videos from artists all over the world.',
                'version' => '1.2.48',
                'size' => '150 MB',
                'developer' => 'Spotify AB',
                'license' => 'Freemium',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => true,
                'downloads' => 89000,
                'rating' => 4.6,
                'reviews_count' => 5600,
                'released_at' => '2026-09-30',
                'tags' => ['Free', 'Popular'],
            ],
            [
                'name' => 'Slack',
                'category' => 'productivity-business',
                'short_description' => 'Team communication platform',
                'description' => 'Slack is a channel-based messaging platform for teams. Bring all your communication together in one place with channels, direct messages, and integrations.',
                'version' => '4.41.105',
                'size' => '195 MB',
                'developer' => 'Salesforce',
                'license' => 'Freemium',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => true,
                'downloads' => 67000,
                'rating' => 4.4,
                'reviews_count' => 3800,
                'released_at' => '2026-09-18',
                'tags' => ['Free', 'Popular'],
            ],
            [
                'name' => '1Password',
                'category' => 'security',
                'short_description' => 'Password manager and secure vault',
                'description' => '1Password is a password manager that keeps your sensitive information safe. Store passwords, credit cards, and secure notes in an encrypted vault.',
                'version' => '8.10.48',
                'size' => '95 MB',
                'developer' => 'AgileBits Inc.',
                'license' => 'Commercial',
                'platform' => 'macOS, Windows',
                'featured' => false,
                'popular' => true,
                'downloads' => 28000,
                'rating' => 4.8,
                'reviews_count' => 1900,
                'released_at' => '2026-09-27',
                'tags' => ['Paid', 'Popular'],
            ],
        ];

        foreach ($apps as $appData) {
            $categorySlug = $appData['category'];
            $category = $categories[$categorySlug];

            $tagNames = $appData['tags'] ?? [];
            unset($appData['category'], $appData['tags']);

            $appData['slug'] = Str::slug($appData['name']);
            $appData['category_id'] = $category->id;
            $appData['published'] = true;

            $app = Application::create($appData);

            if (! empty($tagNames)) {
                $tagIds = [];
                foreach ($tagNames as $tagName) {
                    if (isset($tags[$tagName])) {
                        $tagIds[] = $tags[$tagName]->id;
                    }
                }
                $app->tags()->attach($tagIds);
            }

            ApplicationVersion::create([
                'application_id' => $app->id,
                'version' => $appData['version'],
                'changelog' => 'Bug fixes and performance improvements.',
                'is_current' => true,
                'released_at' => $appData['released_at'],
            ]);
        }
    }
}
