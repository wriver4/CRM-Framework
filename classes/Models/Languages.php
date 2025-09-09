<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * Languages Model
 * 
 * Manages system languages and user language preferences
 * 
 * Methods:
 * - getAllLanguages()
 * - getActiveLanguages()
 * - getLanguageById($id)
 * - getLanguageByLocale($locale)
 * - getDefaultLanguage()
 * - updateLanguage($id, $data)
 * - activateLanguage($id)
 * - deactivateLanguage($id)
 * - setDefaultLanguage($id)
 * - getUserLanguage($userId)
 * - updateUserLanguage($userId, $languageId)
 * - getLanguageFile($languageId)
 * - validateLanguageFile($fileName)
 */

class Languages extends Database
{
    public function __construct()
    {
        parent::__construct($this->dbcrm());
    }

    /**
     * Get all languages in the system
     * @return array
     */
    public function getAllLanguages()
    {
        $sql = "SELECT * FROM languages ORDER BY name_english ASC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get only active languages
     * @return array
     */
    public function getActiveLanguages()
    {
        $sql = "SELECT * FROM languages WHERE is_active = 1 ORDER BY is_default DESC, name_english ASC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get language by ID
     * @param int $id
     * @return array|false
     */
    public function getLanguageById($id)
    {
        $sql = "SELECT * FROM languages WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get language by locale code
     * @param string $locale
     * @return array|false
     */
    public function getLanguageByLocale($locale)
    {
        $sql = "SELECT * FROM languages WHERE locale_code = :locale AND is_active = 1";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':locale', $locale);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get the default system language
     * @return array|false
     */
    public function getDefaultLanguage()
    {
        $sql = "SELECT * FROM languages WHERE is_default = 1 LIMIT 1";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update language settings
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateLanguage($id, $data)
    {
        $allowedFields = ['name_english', 'name_native', 'file_name', 'is_active'];
        $updateFields = [];
        $params = [':id' => $id];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "`{$field}` = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE languages SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Activate a language
     * @param int $id
     * @return bool
     */
    public function activateLanguage($id)
    {
        $sql = "UPDATE languages SET is_active = 1 WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Deactivate a language (cannot deactivate default language)
     * @param int $id
     * @return bool
     */
    public function deactivateLanguage($id)
    {
        // Check if this is the default language
        $language = $this->getLanguageById($id);
        if ($language && $language['is_default'] == 1) {
            return false; // Cannot deactivate default language
        }

        $sql = "UPDATE languages SET is_active = 0 WHERE id = :id AND is_default = 0";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Set a language as the system default
     * @param int $id
     * @return bool
     */
    public function setDefaultLanguage($id)
    {
        try {
            $this->dbcrm()->beginTransaction();

            // First, remove default flag from all languages
            $sql1 = "UPDATE languages SET is_default = 0";
            $stmt1 = $this->dbcrm()->prepare($sql1);
            $stmt1->execute();

            // Set the new default language and ensure it's active
            $sql2 = "UPDATE languages SET is_default = 1, is_active = 1 WHERE id = :id";
            $stmt2 = $this->dbcrm()->prepare($sql2);
            $stmt2->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt2->execute();

            $this->dbcrm()->commit();
            return $result;
        } catch (Exception $e) {
            $this->dbcrm()->rollBack();
            return false;
        }
    }

    /**
     * Get user's language preference
     * @param int $userId
     * @return array|false
     */
    public function getUserLanguage($userId)
    {
        $sql = "SELECT l.* FROM languages l 
                INNER JOIN users u ON l.id = u.language_id 
                WHERE u.id = :user_id AND l.is_active = 1";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If user has no language set or language is inactive, return default
        if (!$result) {
            return $this->getDefaultLanguage();
        }
        
        return $result;
    }

    /**
     * Update user's language preference
     * @param int $userId
     * @param int $languageId
     * @return bool
     */
    public function updateUserLanguage($userId, $languageId)
    {
        // Verify the language exists and is active
        $language = $this->getLanguageById($languageId);
        if (!$language || $language['is_active'] != 1) {
            return false;
        }

        $sql = "UPDATE users SET language_id = :language_id WHERE id = :user_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get the file path for a language
     * @param int $languageId
     * @return string|false
     */
    public function getLanguageFile($languageId)
    {
        $language = $this->getLanguageById($languageId);
        if (!$language) {
            return false;
        }

        $filePath = LANG . '/' . $language['file_name'];
        return file_exists($filePath) ? $filePath : false;
    }

    /**
     * Validate if a language file exists
     * @param string $fileName
     * @return bool
     */
    public function validateLanguageFile($fileName)
    {
        $filePath = LANG . '/' . $fileName;
        return file_exists($filePath) && is_readable($filePath);
    }

    /**
     * Get language options for HTML select
     * @param int $selectedId
     * @return string
     */
    public function getLanguageOptionsHtml($selectedId = null)
    {
        $languages = $this->getActiveLanguages();
        $html = '';
        
        foreach ($languages as $language) {
            $selected = ($selectedId == $language['id']) ? ' selected' : '';
            $html .= '<option value="' . $language['id'] . '"' . $selected . '>';
            $html .= htmlspecialchars($language['name_native']);
            $html .= '</option>';
        }
        
        return $html;
    }

    /**
     * Get browser language preference and match to available languages
     * @return array|false
     */
    public function getBrowserLanguage()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return $this->getDefaultLanguage();
        }

        $browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        
        foreach ($browserLanguages as $lang) {
            $lang = trim(explode(';', $lang)[0]); // Remove quality values
            
            // Try exact match first (e.g., en-US)
            $language = $this->getLanguageByLocale($lang);
            if ($language) {
                return $language;
            }
            
            // Try language code only (e.g., en from en-US)
            $langCode = explode('-', $lang)[0];
            $sql = "SELECT * FROM languages WHERE iso_code = :iso_code AND is_active = 1 ORDER BY is_default DESC LIMIT 1";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->bindParam(':iso_code', $langCode);
            $stmt->execute();
            $language = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($language) {
                return $language;
            }
        }
        
        return $this->getDefaultLanguage();
    }
}