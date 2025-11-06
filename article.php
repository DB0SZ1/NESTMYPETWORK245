<?php
// Include database connection
require 'db.php';

// 1. Get the ID from the URL and validate it
$post_id = null;
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $post_id = (int)$_GET['id'];
}

// 2. --- Find the post in the database ---
$post = null;
if ($post_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching blog post ID $post_id: " . $e->getMessage());
    }
}

// Helper function to calculate reading time
function calculateReadingTime($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words/min
    return max(1, $reading_time); // Minimum 1 minute
}

// Get related posts (exclude current post)
$related_posts = [];
if ($post) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id != ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$post_id]);
        $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching related posts: " . $e->getMessage());
    }
}

// Set the page title
$pageTitle = $post ? htmlspecialchars($post['title']) : "Article Not Found";
include 'header.php';
?>

<style>
/* --- ENHANCED ARTICLE PAGE STYLES --- */

/* Article Meta Information */
.article-meta {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 1.5rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    flex-wrap: wrap;
}

.article-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #555;
    font-size: 0.95rem;
}

.article-meta-item i {
    color: #F2941E;
    font-size: 1rem;
}

.meta-author {
    font-weight: 600;
    color: #2c3e50;
}

/* Enhanced Article Content Typography */
.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #555;
}

.article-content h2 {
    font-size: 1.8rem;
    color: #2c3e50;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f8f9fa;
}

.article-content h3 {
    font-size: 1.4rem;
    color: #2c3e50;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}

.article-content p {
    margin-bottom: 1.5rem;
    text-align: justify;
}

.article-content ul,
.article-content ol {
    list-style-position: outside;
    padding-left: 2rem;
    margin-bottom: 1.5rem;
}

.article-content ul {
    list-style-type: disc;
}

.article-content ol {
    list-style-type: decimal;
}

.article-content li {
    margin-bottom: 0.75rem;
    line-height: 1.8;
}

.article-content blockquote {
    border-left: 4px solid #F2941E;
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-style: italic;
    color: #555;
    background-color: #f8f9fa;
    padding: 1rem 1.5rem;
    border-radius: 8px;
}

.article-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.article-content pre {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    overflow-x: auto;
    margin-bottom: 1.5rem;
}

.article-content pre code {
    background: none;
    padding: 0;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 2rem 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.article-content a {
    color: #2ecc71;
    text-decoration: underline;
    font-weight: 600;
}

.article-content a:hover {
    color: #27ae60;
}

/* Article Footer */
.article-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #e0e0e0;
    flex-wrap: wrap;
    gap: 1rem;
}

.article-footer .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

/* Related Posts Section */
.related-posts {
    margin-top: 4rem;
    padding-top: 3rem;
    border-top: 2px solid #e0e0e0;
}

.related-posts h2 {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 2rem;
    text-align: center;
}

.related-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
}

.related-post-card {
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
}

.related-post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-color: #2ecc71;
}

.related-post-image {
    width: 100%;
    height: 180px;
    overflow: hidden;
}

.related-post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.related-post-card:hover .related-post-image img {
    transform: scale(1.05);
}

.related-post-content {
    padding: 1.5rem;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.related-post-content h3 {
    font-size: 1.2rem;
    color: #2c3e50;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.related-post-content p {
    font-size: 0.95rem;
    color: #555;
    margin-bottom: 1rem;
    flex-grow: 1;
}

.related-post-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.85rem;
    color: #555;
    border-top: 1px solid #e0e0e0;
    padding-top: 1rem;
}

.related-post-meta span {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.related-post-meta i {
    color: #F2941E;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .article-meta {
        flex-direction: column;
        gap: 0.75rem;
        align-items: center;
    }
    
    .article-footer {
        flex-direction: column;
    }
    
    .article-footer .btn {
        width: 100%;
        justify-content: center;
    }
    
    .related-posts-grid {
        grid-template-columns: 1fr;
    }
    
    .article-content {
        font-size: 1rem;
    }
    
    .article-content p {
        text-align: left;
    }
}
</style>

<main class="article-page">
    <div class="container article-container">

        <?php if ($post): ?>
            <!-- Display post -->
            <header class="article-header">
                <h1><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <!-- Article Meta Information -->
                <div class="article-meta">
                    <div class="article-meta-item">
                        <i class="fa-solid fa-user"></i>
                        <span class="meta-author"><?php echo htmlspecialchars($post['author'] ?? 'Admin'); ?></span>
                    </div>
                    <div class="article-meta-item">
                        <i class="fa-solid fa-calendar"></i>
                        <span class="meta-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                    <div class="article-meta-item">
                        <i class="fa-solid fa-clock"></i>
                        <span class="meta-reading-time"><?php echo calculateReadingTime($post['content']); ?> min read</span>
                    </div>
                </div>
            </header>
            
            <div class="article-image">
                <img src="<?php echo htmlspecialchars($post['image_url'] ?: 'https://placehold.co/800x400/e8f5e9/333?text=Image+Not+Found'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" onerror="this.onerror=null;this.src='https://placehold.co/800x400/e8f5e9/333?text=Image+Not+Found';">
            </div>

            <div class="article-content">
                <?php echo $post['content']; // Assumes content contains safe HTML from your admin panel ?>
            </div>

            <!-- Article Footer -->
            <div class="article-footer">
                <a href="index.php#blog" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Back to all articles
                </a>
                <a href="index.php#blog" class="btn btn-secondary">
                    <i class="fa-solid fa-newspaper"></i> View all blog posts
                </a>
            </div>

            <!-- Related Posts Section -->
            <?php if (!empty($related_posts)): ?>
            <section class="related-posts">
                <h2>Related Articles</h2>
                <div class="related-posts-grid">
                    <?php foreach ($related_posts as $related): ?>
                    <a href="article.php?id=<?php echo $related['id']; ?>" class="related-post-card">
                        <div class="related-post-image">
                            <img src="<?php echo htmlspecialchars($related['image_url'] ?: 'https://placehold.co/400x200/e8f5e9/333?text=Image'); ?>" 
                                 alt="<?php echo htmlspecialchars($related['title']); ?>"
                                 onerror="this.onerror=null;this.src='https://placehold.co/400x200/e8f5e9/333?text=Image';">
                        </div>
                        <div class="related-post-content">
                            <h3><?php echo htmlspecialchars($related['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr(strip_tags($related['content']), 0, 100)) . '...'; ?></p>
                            <div class="related-post-meta">
                                <span><i class="fa-solid fa-calendar"></i> <?php echo date('M j, Y', strtotime($related['created_at'])); ?></span>
                                <span><i class="fa-solid fa-clock"></i> <?php echo calculateReadingTime($related['content']); ?> min</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

        <?php else: ?>
            <!-- If no post is found or ID was invalid -->
            <h1>Article Not Found</h1>
            <p>Sorry, we couldn't find the article you were looking for. It may have been removed or the link is incorrect.</p>
            <a href="index.php#blog" class="btn btn-outline">&larr; Back to all articles</a>
        <?php endif; ?>

    </div>
</main>

<?php
include 'footer.php';
?>