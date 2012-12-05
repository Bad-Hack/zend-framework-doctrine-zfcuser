<?php

namespace FileBank;

use FileBank\Entity\File;
use FileBank\Entity\Keyword;
use Doctrine\ORM\Tools\SchemaValidator;

class Manager
{
    /**
     * @var Array 
     */
    protected $params;

    /**            
     * @var Doctrine\ORM\EntityManager
     */                
    protected $em;
    
    /**
     * @var array
     */
    protected $cache;
    
    /**
     * @var FileBank\Entity\File
     */
    protected $file;
    
    /**
     * Set the Module specific configuration parameters
     * 
     * @param Array $params
     * @param Doctrine\ORM\EntityManager $em 
     */
    public function __construct($params, $em) {
        $this->params = $params;
        $this->em = $em;
        $this->cache = array();
    }

    /**
     * Get the FileBank's root folder
     * 
     * @return string 
     */
    public function getRoot() 
    {
        return $this->params['filebank_folder'];
    }
    
    /**
     * Get the file entity based on ID
     * 
     * @param integer $fileId
     * @return FileBank\Entity\File 
     * @throws \Exception 
     */
    
    public function getFileById($fileId)
    {
        // Get the entity from cache if available
        if (isset($this->cache[$fileId])) {
            $entity = $this->cache[$fileId];
        } else {
            $entity = $this->em->find('FileBank\Entity\File', $fileId);
        }
        
        if (!$entity) {
            throw new \Exception('File does not exist.', 404);
        }
        
        // Cache the file entity so we don't have to access db on each call
        // Enables to get multiple entity's properties at different times
        $this->cache[$fileId] = $entity;
        return $entity;
    }
    
    /**
     * Get array of file entities based on given keyword
     * 
     * @param Array $keywords
     * @return Array
     * @throws \Exception 
     */
    public function getFilesByKeywords($keywords)
    {
        // Create unique ID of the array for cache
        $id = md5(serialize($keywords));
        
        // Change all given keywords to lowercase
        $keywords = array_map('strtolower', $keywords );
        
        // Get the entity from cache if available
        if (isset($this->cache[$id])) {
            $entities = $this->cache[$id];
        } else {
            $list = "'" . implode("','", $keywords) . "'";
            
            $q = $this->em->createQuery(
                    "select f from FileBank\Entity\File f, FileBank\Entity\Keyword k
                     where k.file = f
                     and k.value in (" . $list . ")"
                    );
            
            $entities = $q->getResult();
            
            return $entities;
        }
        
        // Cache the file entity so we don't have to access db on each call
        // Enables to get multiple entity's properties at different times
        $this->cache[$id] = $entities;
        return $entities;
    }
    
    /**
     * Save file to FileBank database
     * 
     * @param string $sourceFilePath
     * @return string $relativeFilePath
     * @throws \Exception 
     */
    public function save($sourceFile , Array $keywords = null)
    {
    	if(is_array($sourceFile)){
    		$sourceFilePath = $sourceFile['tmp_name'];
    		$sourceFileName = $sourceFile['name']; 
    	} else {
    		$sourceFilePath = $sourceFile;
    		$sourceFileName = $sourceFile;
    	}
        $fileName = basename($sourceFileName);
        $mimetype = mime_content_type($sourceFilePath);
        $hash     = md5(microtime(true) . $fileName);
        $savePath = substr($hash,0,1).'/'.substr($hash,1,1).'/';

        $this->file = new File();
        $this->file->setName($fileName);
        $this->file->setMimetype($mimetype);
        $this->file->setSize(filesize($sourceFilePath));
        $this->file->setIsActive($this->params['default_is_active']);
        $this->file->setSavepath($savePath . $hash);
        $this->addKeywordsToFile($keywords);
        
        $this->em->persist($this->file);
        $this->em->flush();
        
        $absolutePath = $this->params['filebank_folder'] . $savePath . $hash;
        
        try {
            $this->createPath($absolutePath, $this->params['chmod'], true);
            copy($sourceFilePath, $absolutePath);
        } catch (Exception $e) {
            throw new \Exception('File cannot be saved.');
        }

        return $this->file;
    }
    
    /**
     * Attach keywords to file entity
     * 
     * @param array $keywords
     * @param FileBank\Entity\File $fileEntity
     * @return FileBank\Entity\File 
     */
    protected function addKeywordsToFile($keywords) 
    {
        $keywordEntities = array();
        
        foreach ($keywords as $word) {
            $keyword = new Keyword();
            $keyword->setValue(strtolower($word));
            $keyword->setFile($this->file);
            $this->em->persist($keyword);
            
            $keywordEntities[] = $keyword;
        }
        $this->file->setKeywords($keywordEntities);
    }
    
    /**
     * Create path recursively
     * 
     * @param string $path
     * @param string $mode
     * @param boolean $isFileIncluded 
     * @return boolean
     */
    protected function createPath($path, $mode, $isFileIncluded)
    {
        if (!is_dir(dirname($path))) {
            if ($isFileIncluded) {
                mkdir(dirname($path), $mode, true);
            } else {
                mkdir($path, $mode, true);
            }
        }
    }
}
