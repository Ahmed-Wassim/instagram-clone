<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Instagram Clone - Feature Documentation

This document outlines the features implemented so far in the Instagram clone Laravel project, including architecture notes, relationships, and implementation highlights.

---

## Technologies Used

### Redis
- Used as an in-memory data store and cache to improve application performance.
- Caches frequently accessed data such as posts, feeds, user info, and explore results.
- Powers real-time broadcasting for notifications through Laravel Echo and WebSockets.
- Used as the queue driver to manage background jobs efficiently.

### Laravel Queues
- Handle time-consuming or resource-heavy tasks asynchronously, improving API responsiveness.
- Examples:
  - Video processing and moving temporary uploads to permanent storage.
  - Deleting expired stories in the background.
  - Sending notifications without blocking HTTP requests.
- Queue worker configured to run with Redis driver.
- Managed within Docker containers via Laravel Sail for smooth local development.

### Notifications
- Laravel's notification system sends real-time alerts for user interactions:
  - New followers
  - Likes on posts, comments, reels
  - Comments on posts and reels
- Uses broadcast channels backed by Redis and Laravel Echo for pushing notifications to clients instantly.
- Supports database notifications to keep a history of alerts.
- Notification events dispatched asynchronously using queues.

### Cron Jobs & Scheduler
- Laravel’s scheduler manages periodic tasks, run every minute by a system cron or via `php artisan schedule:work`.
- Used for:
  - Regularly running queued jobs (e.g., story expiry cleanup).
  - Scheduling batch processes such as cleaning temp video files.
- Scheduler runs inside a dedicated Docker container in the Sail environment.

---

## 1. User Authentication
- **Features:**
  - User registration with avatar image upload
  - Login, logout, and token refresh using Laravel Sanctum (or JWT)
  - User profile editing: update name, bio, website, avatar
- **Implementation Notes:**
  - Password hashing and validation on registration
  - Avatar stored in `storage/app/public/avatars`
  - Validation rules ensure required fields and image formats
  - Profile update supports multipart form-data to handle file uploads

---

## 2. Posts
- **Features:**
  - CRUD operations on posts
  - Support for multiple photos per post using polymorphic `images` relation
  - Post captions with slug generation for SEO-friendly URLs
  - Post status for future extensibility (e.g., draft, published)
- **Caching:**
  - Redis used to cache individual posts, posts by slug, and paginated user/all posts feeds
  - Cache keys carefully invalidated on create/update/delete actions
- **Database:**
  - Posts table with fields: `id`, `user_id`, `caption`, `slug`, `status`, `timestamps`
  - Images table polymorphically linked with posts
- **API Endpoints:**
  - Create, update, delete posts with images
  - Fetch posts feed with eager loading for user, images, likes, comments counts

---

## 3. Likes
- **Features:**
  - Users can like/unlike posts, comments, and reels
  - Polymorphic likes relation to attach likes to multiple models
- **Database:**
  - `likes` table with `user_id`, `likeable_id`, `likeable_type`
- **API:**
  - Endpoints for liking/unliking entities
  - Optimized queries for checking if the current user liked a post/comment/reel

---

## 4. Comments
- **Features:**
  - Polymorphic comments on posts and reels
  - Create, update (optional), and delete comments
- **Database:**
  - `comments` table with `user_id`, `commentable_id`, `commentable_type`, `content`
- **API:**
  - Comments nested under posts or reels
  - Fetch comments with commenter info
- **Validation:**
  - Ensures comment content is not empty

---

## 5. Follow System
- **Features:**
  - Users can follow and unfollow each other
  - Fetch followers and followings lists
  - Flag `is_followed` to indicate if authenticated user follows the profile user
- **Database:**
  - `follows` pivot table: `follower_id`, `following_id`, timestamps
- **API:**
  - Follow/unfollow endpoints
  - Efficient eager loading of followers/followings with user info

---

## 6. Reels (Videos)
- **Features:**
  - Upload short video reels
  - Temporary upload storage, then background queue jobs move videos to permanent reels storage
  - Polymorphic likes and comments on reels
- **Video Processing:**
  - Use Laravel queues and cron jobs to process and move video files asynchronously
- **Notifications:**
  - Real-time notifications on reel likes and comments
- **Storage:**
  - Videos stored under `storage/app/public/reels`
- **API:**
  - Endpoints to upload, fetch reels feed, and interact with reels

---

## 7. Stories
- **Features:**
  - Upload image and video stories
  - Stories auto-delete after expiry (e.g., 24 hours)
  - Different logic for images (simple upload) vs videos (queued background processing)
- **Queues:**
  - Use Laravel queue worker to handle story deletion asynchronously
- **Database:**
  - `stories` table with polymorphic relation to user and media (image/video)
- **API:**
  - Fetch current stories for the user’s feed
  - Upload and delete stories endpoints

---

## 8. Notifications
- **Features:**
  - Real-time notifications using Laravel Notifications and broadcasting with Redis and Pusher (or Laravel Websockets)
  - Notifications for:
    - New followers
    - Likes on posts, comments, reels
    - Comments on posts and reels
- **Implementation:**
  - Use Redis as broadcast queue driver for real-time push
  - Notification channels configured for database and broadcast
- **API:**
  - Fetch notifications list
  - Mark notifications as read

---

## 9. Search
- **Features:**
  - Search users by username, name
  - Search posts by caption text or hashtags (optional extension)
- **Implementation:**
  - Simple SQL `LIKE` queries or optionally integrate full-text search engines like Algolia or Scout
- **API:**
  - Unified search endpoint with filters for users/posts

---

## 10. Caching
- **Features:**
  - Redis caching integrated across feeds, posts, explore pages, and other high-traffic endpoints
  - Cache invalidation on updates and deletes to maintain data consistency
- **Performance:**
  - Significantly reduces DB queries and improves response time

---

## 11. Queues & Scheduler
- **Features:**
  - Queue workers for background jobs:
    - Video processing
    - Story expiry cleanup
    - Sending notifications asynchronously
  - Scheduler to run periodic tasks (cleanups, stats, etc.)
- **Docker Integration:**
  - Queue worker and scheduler run in dedicated Docker containers using Laravel Sail
- **Commands:**
  - `php artisan queue:work redis --sleep=3 --tries=3 --timeout=60`
  - `php artisan schedule:work`

---

# Summary

You have built a robust Instagram clone backend featuring all major social media components:

- Authentication & user profiles
- Posts with photos & videos (reels)
- Likes, comments, and follows
- Stories with timed deletion
- Real-time notifications
- Search functionality
- Performance optimizations with Redis caching
- Background processing with queues and scheduler
