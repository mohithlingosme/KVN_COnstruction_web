<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Repository.php';

class BlogRepository extends Repository
{
    protected string $table = 'blogs';

    private ?PDO $pdo = null;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->pdo = $db;
            parent::__construct($db);
        } else {
            $conn = \App\Core\Database::getInstance()->getConnection();
            if ($conn) {
                $this->pdo = $conn;
                parent::__construct($conn);
            }
        }
    }

    public function findPublished(): array
    {
        return $this->findBy(['status' => 'published'], 'published_at DESC');
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM blogs WHERE slug = :slug AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findFeatured(): array
    {
        return $this->findBy(['is_featured' => 1, 'status' => 'published'], 'published_at DESC');
    }

    public function findByCategory(int $categoryId): array
    {
        return $this->findBy(['category_id' => $categoryId, 'status' => 'published'], 'published_at DESC');
    }

    public function incrementViews(int $id): void
    {
        $this->db->prepare("UPDATE blogs SET views_count = views_count + 1 WHERE id = :id")
            ->execute([':id' => $id]);
    }

    public function search(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM blogs 
             WHERE deleted_at IS NULL AND status = 'published'
             AND (title LIKE :query OR content LIKE :query OR excerpt LIKE :query OR tags LIKE :query)
             ORDER BY published_at DESC LIMIT 20"
        );
        $stmt->execute([':query' => $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategories(): array
    {
        $stmt = $this->db->query(
            "SELECT bc.*, COUNT(b.id) as post_count 
             FROM blog_categories bc 
             LEFT JOIN blogs b ON b.category_id = bc.id AND b.deleted_at IS NULL AND b.status = 'published'
             WHERE bc.deleted_at IS NULL AND bc.status = 'active'
             GROUP BY bc.id ORDER BY bc.category_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function getTags(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM blog_tags WHERE deleted_at IS NULL AND status = 'active' ORDER BY tag_name ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================================================
    // ADMIN METHODS
    // ========================================================================

    /**
     * Get all blogs with author info for admin listing
     */
    public function findAllWithAuthor(): array
    {
        $stmt = $this->db->prepare(
            "SELECT b.*, u.full_name AS author_name
             FROM blogs b
             LEFT JOIN users u ON b.author_id = u.id
             ORDER BY b.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find blog by ID for admin
     */
    public function findByIdAdmin(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM blogs WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Insert a blog post
     */
    public function insertBlog(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO blogs (title, slug, category, excerpt, content, featured_image, meta_title, meta_description, tags, status, is_featured, author_id, created_at, updated_at)
             VALUES (:title, :slug, :category, :excerpt, :content, :featured_image, :meta_title, :meta_description, :tags, :status, :is_featured, :author_id, NOW(), NOW())"
        );
        $stmt->execute([
            ':title' => $data['title'] ?? '',
            ':slug' => $data['slug'] ?? '',
            ':category' => $data['category'] ?? 'General',
            ':excerpt' => $data['excerpt'] ?? '',
            ':content' => $data['content'] ?? '',
            ':featured_image' => $data['featured_image'] ?? '',
            ':meta_title' => $data['meta_title'] ?? '',
            ':meta_description' => $data['meta_description'] ?? '',
            ':tags' => $data['tags'] ?? '',
            ':status' => $data['status'] ?? 'draft',
            ':is_featured' => (int)($data['is_featured'] ?? 0),
            ':author_id' => (int)($data['author_id'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a blog post
     */
    public function updateBlog(int $id, array $data): bool
    {
        $sets = [];
        $params = [':id' => $id];
        $allowed = ['title', 'slug', 'category', 'excerpt', 'content', 'featured_image', 'meta_title', 'meta_description', 'tags', 'status', 'is_featured'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $sets[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }
        if (empty($sets)) {
            return false;
        }
        $setClause = implode(', ', $sets);
        $stmt = $this->db->prepare(
            "UPDATE blogs SET {$setClause}, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute($params);
    }

    /**
     * Delete a blog post (hard delete)
     */
    public function deleteBlog(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blogs WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ========================================================================
    // BLOG CATEGORIES (admin)
    // ========================================================================

    /**
     * Get all blog categories with blog count
     */
    public function getAllCategories(): array
    {
        $stmt = $this->db->prepare(
            "SELECT bc.*, (SELECT COUNT(*) FROM blogs b WHERE b.category = bc.category_name) AS blog_count
             FROM blog_categories bc
             ORDER BY bc.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find blog category by slug
     */
    public function findCategoryBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT id FROM blog_categories WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Insert a blog category
     */
    public function insertCategory(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO blog_categories (category_name, slug, description, status, created_at) VALUES (:category_name, :slug, :description, :status, NOW())"
        );
        $stmt->execute([
            ':category_name' => $data['category_name'] ?? '',
            ':slug' => $data['slug'] ?? '',
            ':description' => $data['description'] ?? '',
            ':status' => $data['status'] ?? 'active',
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete a blog category
     */
    public function deleteCategory(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ========================================================================
    // BLOG TAGS (admin)
    // ========================================================================

    /**
     * Get all blog tags with blog count
     */
    public function getAllTags(): array
    {
        $stmt = $this->db->prepare(
            "SELECT bt.*, (SELECT COUNT(*) FROM blogs b WHERE b.tags LIKE CONCAT('%', bt.tag_name, '%')) AS blog_count
             FROM blog_tags bt
             ORDER BY bt.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find blog tag by slug
     */
    public function findTagBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT id FROM blog_tags WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Insert a blog tag
     */
    public function insertTag(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO blog_tags (tag_name, slug, description, status, created_at) VALUES (:tag_name, :slug, :description, :status, NOW())"
        );
        $stmt->execute([
            ':tag_name' => $data['tag_name'] ?? '',
            ':slug' => $data['slug'] ?? '',
            ':description' => $data['description'] ?? '',
            ':status' => $data['status'] ?? 'active',
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete a blog tag
     */
    public function deleteTag(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_tags WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Find tag by ID
     */
    public function findTagById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM blog_tags WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // ========================================================================
    // BLOG COMMENTS (admin)
    // ========================================================================

    /**
     * Get all comments with blog info
     */
    public function getAllComments(): array
    {
        $stmt = $this->db->prepare(
            "SELECT bc.*, b.title AS blog_title, b.slug AS blog_slug
             FROM blog_comments bc
             LEFT JOIN blogs b ON bc.blog_id = b.id
             ORDER BY bc.id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update comment status
     */
    public function updateCommentStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE blog_comments SET status = :status, updated_at = NOW() WHERE id = :id"
        );
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM blog_comments WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
