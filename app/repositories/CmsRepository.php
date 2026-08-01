<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use Exception;
use App\Core\Database;

/**
 * Enterprise CMS Repository
 * All SQL related to CMS pages: about_page, contact_page, homepage_content,
 * seo_settings, about_advantages, about_process_steps, about_specifications,
 * contact_page_features.
 */
class CmsRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
        } else {
            $conn = Database::getInstance()->getConnection();
            if (!$conn) {
                throw new Exception("Database connection unavailable in CmsRepository.");
            }
            $this->db = $conn;
        }
    }

    // ========================================================================
    // ABOUT PAGE
    // ========================================================================

    public function getAboutPage(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM about_page LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('CmsRepository::getAboutPage error: ' . $e->getMessage());
            return null;
        }
    }

    public function aboutPageExists(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM about_page LIMIT 1");
            $stmt->execute();
            return (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertAboutPage(array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO about_page
                    (hero_title, hero_description, mission_title, mission_content,
                     vision_title, vision_content, process_content, why_choose_content)
                 VALUES
                    (:hero_title, :hero_description, :mission_title, :mission_content,
                     :vision_title, :vision_content, :process_content, :why_choose_content)"
            );
            return $stmt->execute([
                ':hero_title'         => $data['hero_title'] ?? '',
                ':hero_description'   => $data['hero_description'] ?? '',
                ':mission_title'      => $data['mission_title'] ?? '',
                ':mission_content'    => $data['mission_content'] ?? '',
                ':vision_title'       => $data['vision_title'] ?? '',
                ':vision_content'     => $data['vision_content'] ?? '',
                ':process_content'    => $data['process_content'] ?? '',
                ':why_choose_content' => $data['why_choose_content'] ?? '',
            ]);
        } catch (\Throwable $e) {
            error_log('CmsRepository::insertAboutPage error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateAboutPage(array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE about_page SET
                    hero_title         = :hero_title,
                    hero_description   = :hero_description,
                    mission_title      = :mission_title,
                    mission_content    = :mission_content,
                    vision_title       = :vision_title,
                    vision_content     = :vision_content,
                    process_content    = :process_content,
                    why_choose_content = :why_choose_content
                 WHERE id = 1"
            );
            return $stmt->execute([
                ':hero_title'         => $data['hero_title'] ?? '',
                ':hero_description'   => $data['hero_description'] ?? '',
                ':mission_title'      => $data['mission_title'] ?? '',
                ':mission_content'    => $data['mission_content'] ?? '',
                ':vision_title'       => $data['vision_title'] ?? '',
                ':vision_content'     => $data['vision_content'] ?? '',
                ':process_content'    => $data['process_content'] ?? '',
                ':why_choose_content' => $data['why_choose_content'] ?? '',
            ]);
        } catch (\Throwable $e) {
            error_log('CmsRepository::updateAboutPage error: ' . $e->getMessage());
            return false;
        }
    }

    public function getAboutAdvantages(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM about_advantages WHERE status = 'active' ORDER BY sort_order ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('CmsRepository::getAboutAdvantages error: ' . $e->getMessage());
            return [];
        }
    }

    public function getAboutProcessSteps(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM about_process_steps WHERE status = 'active' ORDER BY sort_order ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('CmsRepository::getAboutProcessSteps error: ' . $e->getMessage());
            return [];
        }
    }

    public function getAboutSpecifications(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM about_specifications WHERE status = 'active' ORDER BY sort_order ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('CmsRepository::getAboutSpecifications error: ' . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // CONTACT PAGE
    // ========================================================================

    public function getContactPage(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM contact_page LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('CmsRepository::getContactPage error: ' . $e->getMessage());
            return null;
        }
    }

    public function contactPageExists(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM contact_page LIMIT 1");
            $stmt->execute();
            return (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertContactPage(array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO contact_page
                    (hero_title, hero_description, phone, email, office_address, office_hours,
                     google_map_link, form_title, form_description, why_choose_title, why_choose_content)
                 VALUES
                    (:hero_title, :hero_description, :phone, :email, :office_address, :office_hours,
                     :google_map_link, :form_title, :form_description, :why_choose_title, :why_choose_content)"
            );
            return $stmt->execute([
                ':hero_title'         => $data['hero_title'] ?? '',
                ':hero_description'   => $data['hero_description'] ?? '',
                ':phone'              => $data['phone'] ?? '',
                ':email'              => $data['email'] ?? '',
                ':office_address'     => $data['office_address'] ?? '',
                ':office_hours'       => $data['office_hours'] ?? '',
                ':google_map_link'    => $data['google_map_link'] ?? '',
                ':form_title'         => $data['form_title'] ?? '',
                ':form_description'   => $data['form_description'] ?? '',
                ':why_choose_title'   => $data['why_choose_title'] ?? '',
                ':why_choose_content' => $data['why_choose_content'] ?? '',
            ]);
        } catch (\Throwable $e) {
            error_log('CmsRepository::insertContactPage error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateContactPage(array $data): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE contact_page SET
                    hero_title         = :hero_title,
                    hero_description   = :hero_description,
                    phone              = :phone,
                    email              = :email,
                    office_address     = :office_address,
                    office_hours       = :office_hours,
                    google_map_link    = :google_map_link,
                    form_title         = :form_title,
                    form_description   = :form_description,
                    why_choose_title   = :why_choose_title,
                    why_choose_content = :why_choose_content
                 WHERE id = 1"
            );
            return $stmt->execute([
                ':hero_title'         => $data['hero_title'] ?? '',
                ':hero_description'   => $data['hero_description'] ?? '',
                ':phone'              => $data['phone'] ?? '',
                ':email'              => $data['email'] ?? '',
                ':office_address'     => $data['office_address'] ?? '',
                ':office_hours'       => $data['office_hours'] ?? '',
                ':google_map_link'    => $data['google_map_link'] ?? '',
                ':form_title'         => $data['form_title'] ?? '',
                ':form_description'   => $data['form_description'] ?? '',
                ':why_choose_title'   => $data['why_choose_title'] ?? '',
                ':why_choose_content' => $data['why_choose_content'] ?? '',
            ]);
        } catch (\Throwable $e) {
            error_log('CmsRepository::updateContactPage error: ' . $e->getMessage());
            return false;
        }
    }

    public function getContactFeatures(): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM contact_page_features WHERE status = 'active' ORDER BY sort_order ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('CmsRepository::getContactFeatures error: ' . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // HOMEPAGE CONTENT
    // ========================================================================

    public function getHomepageContent(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM homepage_content LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('CmsRepository::getHomepageContent error: ' . $e->getMessage());
            return null;
        }
    }

    public function homepageContentExists(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM homepage_content LIMIT 1");
            $stmt->execute();
            return (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertHomepageContent(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO homepage_content (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::insertHomepageContent error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateHomepageContent(array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE homepage_content SET " . implode(', ', $sets) . " WHERE id = 1";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::updateHomepageContent error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // SEO SETTINGS
    // ========================================================================

    public function getSeoSettings(): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM seo_settings LIMIT 1");
            $stmt->execute();
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            error_log('CmsRepository::getSeoSettings error: ' . $e->getMessage());
            return null;
        }
    }

    public function seoSettingsExists(): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM seo_settings LIMIT 1");
            $stmt->execute();
            return (bool)$stmt->fetch();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function insertSeoSettings(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO seo_settings (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::insertSeoSettings error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateSeoSettings(array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE seo_settings SET " . implode(', ', $sets) . " WHERE id = 1";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::updateSeoSettings error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all SEO settings (multi-page support for CMS).
     */
    public function getAllSeoSettings(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM seo_settings ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('CmsRepository::getAllSeoSettings error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Update SEO settings by page ID.
     */
    public function updateSeoById(int $id, array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE seo_settings SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            $params[':id'] = $id;
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::updateSeoById error: ' . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // FAQS
    // ========================================================================

    public function getAllFaqs(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM faqs ORDER BY display_order ASC, id DESC");
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            error_log('CmsRepository::getAllFaqs error: ' . $e->getMessage());
            return [];
        }
    }

    public function getFaqById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM faqs WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $res = $stmt->fetch();
            return $res ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function insertFaq(array $data): bool
    {
        try {
            $cols = array_keys($data);
            $placeholders = array_map(fn($c) => ':' . $c, $cols);
            $sql = "INSERT INTO faqs (" . implode(', ', $cols) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::insertFaq error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateFaq(int $id, array $data): bool
    {
        try {
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
            $sql = "UPDATE faqs SET " . implode(', ', $sets) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $params = [];
            foreach ($data as $k => $v) {
                $params[':' . $k] = $v;
            }
            $params[':id'] = $id;
            return $stmt->execute($params);
        } catch (\Throwable $e) {
            error_log('CmsRepository::updateFaq error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteFaq(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM faqs WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('CmsRepository::deleteFaq error: ' . $e->getMessage());
            return false;
        }
    }
}