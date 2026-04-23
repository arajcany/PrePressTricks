<?php

namespace arajcany\FreeFlowCoreAssistant\Configuration;

use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Filesystem\Folder;
use Cake\I18n\Time;
use Cake\Utility\Xml;
use DOMDocument;
use SimpleXMLElement;

/**
 * Class FreeFlowCoreConfigBase
 *
 * @property string $ffcHome
 * @property string $ffcUrl
 * @property string $defaultTenantId
 *
 * @package arajcany\FreeFlowCoreAssistant\Configuration
 */
class FreeFlowCoreConfigBase
{
    private string $ffcHome;
    private string $ffcUrl;
    private string $defaultTenantId;

    /**
     * Default constructor.
     * @param null $ffcHome
     * @param null $defaultTenantId
     * @param null $ffcUrl
     */
    public function __construct($ffcHome = null, $defaultTenantId = null, $ffcUrl = null)
    {
        //setup a TMP directory if not defined
        if (!defined('TMP')) {
            define('TMP', __DIR__ . "\\..\\..\\tmp\\");
        }

        //getenv('XRX_TENANTSHOMEROOT');
        //getenv('XRX_I2S_DATAPATH');
        $ffcHomeEnv = getenv('FF_CORE_HOME');


        //setup HOME directory
        if ($ffcHome && is_dir($ffcHome)) {
            $this->ffcHome = TextFormatter::makeEndsWith($ffcHome, "\\");
        } elseif ($ffcHomeEnv && is_dir($ffcHomeEnv)) {
            $this->ffcHome = TextFormatter::makeEndsWith($ffcHomeEnv, "\\");
        } elseif (defined('FF_CORE_HOME')) {
            if (is_dir(FF_CORE_HOME)) {
                $this->ffcHome = TextFormatter::makeEndsWith(FF_CORE_HOME, "\\");
            }
        } else {
            return false;
        }


        //setup default TENANT
        $tenants = $this->_getTenants();
        if ($tenants === false) {
            return false;
        } elseif (is_array($tenants)) {
            if (count($tenants) == 0) {
                return false;
            } elseif (count($tenants) > 0) {
                if ($defaultTenantId) {
                    if (isset($tenants[$defaultTenantId])) {
                        $this->defaultTenantId = $defaultTenantId;
                    } else {
                        return false;
                    }
                } else {
                    $this->defaultTenantId = array_keys($tenants)[0];
                }
            }
        } else {
            return false;
        }

        //setup default URL
        if ($ffcUrl) {
            if (!TextFormatter::startsWith($ffcHome, "https://")) {
                if (!TextFormatter::startsWith($ffcHome, "http://")) {
                    $ffcUrl = TextFormatter::makeStartsWith($ffcHome, "http://");
                }
            }
            $this->ffcUrl = TextFormatter::makeEndsWith($ffcUrl, "/");
        } elseif (defined('FF_CORE_URL')) {
            if (!TextFormatter::startsWith(FF_CORE_URL, "https://")) {
                if (!TextFormatter::startsWith(FF_CORE_URL, "http://")) {
                    $ffcUrl = TextFormatter::makeStartsWith(FF_CORE_URL, "http://");
                }
            }
            $this->ffcUrl = TextFormatter::makeEndsWith(FF_CORE_URL, "/");
        } else {
            $this->ffcUrl = "http://localhost/";
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFfcHome(): string
    {
        return $this->ffcHome;
    }

    /**
     * @return string
     */
    public function getFfcUrl(): string
    {
        return $this->ffcUrl;
    }

    /**
     * @return string
     */
    public function getDefaultTenantId(): string
    {
        return $this->defaultTenantId;
    }

    /**
     * Wrapper function
     *
     * @return array|bool
     */
    public function getTenants()
    {
        return $this->_getTenants();
    }


    /**
     * Return all the FFC tenants as Key/Value pair
     * Key - Tenant ID
     * Value - Tenant full folder path
     *
     * @return array|bool
     */
    private function _getTenants()
    {
        /**
         * @var array $setting
         */

        $mantisConfig = $this->_readAppConfigFile("MantisService.exe.config", 'array');

        $mantisConfigAppSettings = [];
        foreach ($mantisConfig['configuration']['appSettings']['add'] as $setting) {
            $mantisConfigAppSettings[$setting['@key']] = $setting['@value'];
        }

        if (!isset($mantisConfigAppSettings['TenantsHomeRoot'])) {
            return false;
        }

        $tenantsHomeRootFolderPath = TextFormatter::makeEndsWith($mantisConfigAppSettings['TenantsHomeRoot'], "\\");
        $tenantsHomeRootObj = new Folder($tenantsHomeRootFolderPath);
        $tenants = $tenantsHomeRootObj->read(true)[0];

        $tenantList = [];
        foreach ($tenants as $tenant) {
            $tenantList[$tenant] = TextFormatter::makeEndsWith($tenantsHomeRootFolderPath . $tenant, "\\");
        }

        ksort($tenantList);
        return $tenantList;
    }


    /**
     * Wrapper function
     *
     * @param null $type
     * @return array|bool
     */
    public function getFileExtensions($type = null)
    {
        return $this->_getFileExtensions($type);
    }


    /**
     * Return all the filetype extensions as an array
     *
     * @param null $types null|SubmitExtensions|OfficeExtensions|VippExtensions|MaxExtensions|ZipExtensions|LegacyVIPPExtensions
     * @return array|bool
     */
    private function _getFileExtensions($types = null)
    {
        if (is_string($types)) {
            $types = [$types];
        }

        $mantisConfig = $this->_readAppConfigFile("MantisCommon.dll.config", 'array');

        $mantisConfigAppSettings = [];
        foreach ($mantisConfig['configuration']['appSettings']['add'] as $setting) {
            $mantisConfigAppSettings[$setting['@key']] = $setting['@value'];
        }

        $extList = '';
        if (isset($mantisConfigAppSettings['SubmitExtensions'])) {
            if ($types == null || in_array('SubmitExtensions', $types)) {
                $extList .= $mantisConfigAppSettings['SubmitExtensions'] . ",";
            }
        }
        if (isset($mantisConfigAppSettings['OfficeExtensions'])) {
            if ($types == null || in_array('OfficeExtensions', $types)) {
                $extList .= $mantisConfigAppSettings['OfficeExtensions'] . ",";
            }
        }
        if (isset($mantisConfigAppSettings['VippExtensions'])) {
            if ($types == null || in_array('VippExtensions', $types)) {
                $extList .= $mantisConfigAppSettings['VippExtensions'] . ",";
            }
        }
        if (isset($mantisConfigAppSettings['MaxExtensions'])) {
            if ($types == null || in_array('MaxExtensions', $types)) {
                $extList .= $mantisConfigAppSettings['MaxExtensions'] . ",";
            }
        }
        if (isset($mantisConfigAppSettings['ZipExtensions'])) {
            if ($types == null || in_array('ZipExtensions', $types)) {
                $extList .= $mantisConfigAppSettings['ZipExtensions'] . ",";
            }
        }
        if (isset($mantisConfigAppSettings['LegacyVIPPExtensions'])) {
            if ($types == null || in_array('LegacyVIPPExtensions', $types)) {
                $extList .= $mantisConfigAppSettings['LegacyVIPPExtensions'] . ",";
            }
        }

        $extList = trim($extList, ",");
        $extList = str_replace(".", "", $extList);
        $extList = explode(",", $extList);
        $extList = array_unique($extList);

        return $extList;
    }


    /**
     * Get the License Features for a specific tenant
     *
     * @param null|string $tenantId
     * @return array|bool
     */
    public function readTenantLicenseFile($tenantId = null)
    {
        //default tenant id
        if (empty($tenantId) || $tenantId === false) {
            $tenantId = $this->defaultTenantId;
        }

        $licenseFileContents = $this->readTenantConfigFile($tenantId, "CoreLicenseFile.dat");
        if ($licenseFileContents) {
            return $this->_parseLicenseFile($licenseFileContents);
        } else {
            return false;
        }
    }


    /**
     * Get the Hot Folders for a specific tenant
     *
     * @param null|string $tenantId
     * @param string $format
     * @return array|bool|DOMDocument|SimpleXMLElement|string
     */
    public function getTenantHotFolders($tenantId = null, $format = 'list')
    {
        //default tenant id
        if (empty($tenantId) || $tenantId === false) {
            $tenantId = $this->defaultTenantId;
        }
        $hotFolderConfigFileContents = $this->readTenantConfigFile($tenantId, "HFList.xml");
        if ($hotFolderConfigFileContents) {
            if ($format == "list") {
                $hotFolders = $this->_reformatRawData($hotFolderConfigFileContents, 'array');
                if (!isset($hotFolders['HotFolders']['HotFolder'])) {
                    return false;
                } elseif ($this->isSeqArr($hotFolders['HotFolders']['HotFolder'])) {
                    $hotFoldersToLoop = $hotFolders['HotFolders']['HotFolder'];
                } else {
                    $hotFoldersToLoop = [$hotFolders['HotFolders']['HotFolder']];
                }
                $toReturn = [];
                foreach ($hotFoldersToLoop as $hotFolder) {
                    $toReturn[$hotFolder['Name']] = TextFormatter::makeEndsWith($hotFolder['Location'], "\\");
                }
                return $toReturn;
            } else {
                return $this->_reformatRawData($hotFolderConfigFileContents, $format);
            }
        } else {
            return false;
        }
    }


    /**
     * Get the Hot Folders for a specific tenant and name
     *
     * @param null|string $tenantId
     * @param null|string $hotFolderName
     * @return array|bool|DOMDocument|SimpleXMLElement|string
     */
    public function getTenantHotFolderDataByName($tenantId = null, $hotFolderName = null)
    {
        //default tenant id
        if (empty($tenantId) || $tenantId === false) {
            $tenantId = $this->defaultTenantId;
        }
        $hotFolderConfigFileContents = $this->readTenantConfigFile($tenantId, "HFList.xml");
        if ($hotFolderConfigFileContents) {
            $hotFolders = $this->_reformatRawData($hotFolderConfigFileContents, 'array');
            if (!isset($hotFolders['HotFolders']['HotFolder'])) {
                return false;
            } elseif ($this->isSeqArr($hotFolders['HotFolders']['HotFolder'])) {
                $hotFoldersToLoop = $hotFolders['HotFolders']['HotFolder'];
            } else {
                $hotFoldersToLoop = [$hotFolders['HotFolders']['HotFolder']];
            }
            $toReturn = false;
            foreach ($hotFoldersToLoop as $hotFolder) {
                if ($hotFolder['Name'] == $hotFolderName) {
                    $toReturn = $hotFolder;
                }
            }
            return $toReturn;
        } else {
            return false;
        }
    }


    /**
     * Wrapper function
     *
     * @param null|string $tenantId
     * @param string $configFilename
     * @param string $format
     * @return array|bool|DOMDocument|SimpleXMLElement|string
     */
    public function readTenantConfigFile($tenantId = null, $configFilename = '', $format = 'raw')
    {
        return $this->_readTenantConfigFile($tenantId, $configFilename, $format);
    }


    /**
     * Read a config file and return the contents in the specified format
     *
     * @param null|string $tenantId
     * @param string $configFilename
     * @param string $format raw|simplexml|domdocument|array|json
     * @return array|bool|DOMDocument|SimpleXMLElement|string
     */
    private function _readTenantConfigFile($tenantId = null, $configFilename = '', $format = 'raw')
    {
        //default tenant id
        if (empty($tenantId) || $tenantId === false) {
            $tenantId = $this->defaultTenantId;
        }

        $configFiles = $this->_findTenantConfigFiles($tenantId);

        if (isset($configFiles[$configFilename])) {
            $contents = file_get_contents($configFiles[$configFilename]);
        } else {
            return false;
        }

        return $this->_reformatRawData($contents, $format);
    }


    /**
     * Wrapper function
     *
     * @param null|string $tenantId
     * @return array
     */
    public function findTenantConfigFiles($tenantId = null)
    {
        return $this->_findTenantConfigFiles($tenantId);
    }


    /**
     * Find all config files in the FFC Tenant directories
     * Returns a key/value pair array
     * `
     *  [
     *   'MantisService.exe.config' => 'K:\Program Files\Xerox\FreeFlow Core\MantisService.exe.config'
     *  ]
     * `
     *
     * @param null|string $tenantId
     * @param null|array $extensions
     * @return array|bool
     */
    private function _findTenantConfigFiles($tenantId = null, $extensions = null)
    {
        //default tenant id
        if (empty($tenantId) || $tenantId === false) {
            $tenantId = $this->defaultTenantId;
        }

        if (empty($extensions) || $extensions === false) {
            $extensions = ["config", "xml", "json", "dat", "xpaf"];
        } elseif (is_string($extensions)) {
            $extensions = [$extensions];
        }

        $tenants = $this->_getTenants();
        if (!isset($tenants[$tenantId])) {
            return false;
        }

        $extensions = implode("|", $extensions);

        $folderObj = new Folder($tenants[$tenantId]);
        $configFiles = $folderObj->findRecursive('.*\.(' . $extensions . ')', true);

        $configFilesReturn = [];
        foreach ($configFiles as $k => $configFile) {
            $configFilesReturn[pathinfo($configFile, PATHINFO_BASENAME)] = $configFile;
        }

        return $configFilesReturn;
    }


    /**
     * Wrapper function
     *
     * @param string $configFilename
     * @param string $format
     * @return array|bool|DOMDocument|SimpleXMLElement|string
     */
    public function readAppConfigFile($configFilename = '', $format = 'raw')
    {
        return $this->_readAppConfigFile($configFilename, $format);
    }


    /**
     * Read a config file and return the contents in the specified format
     *
     * @param string $configFilename
     * @param string $format raw|simplexml|domdocument|array|json
     * @return array|bool|DOMDocument|SimpleXMLElement|string
     */
    private function _readAppConfigFile($configFilename = '', $format = 'raw')
    {
        $configFiles = $this->_findAppConfigFiles();

        if (isset($configFiles[$configFilename])) {
            $contents = file_get_contents($configFiles[$configFilename]);
        } else {
            return false;
        }

        return $this->_reformatRawData($contents, $format);
    }


    /**
     * Wrapper function
     *
     * @return array
     */
    public function findAppConfigFiles()
    {
        return $this->_findAppConfigFiles();
    }


    /**
     * Find all config files in the FFC App directories
     * Returns a key/value pair array
     * `
     *  [
     *   'MantisService.exe.config' => 'K:\Program Files\Xerox\FreeFlow Core\MantisService.exe.config'
     *  ]
     * `
     *
     * @param null $extensions
     * @return array
     */
    private function _findAppConfigFiles($extensions = null)
    {
        if (empty($extensions) || $extensions === false) {
            $extensions = ["config", "xml", "json"];
        } elseif (is_string($extensions)) {
            $extensions = [$extensions];
        }

        $extensions = implode("|", $extensions);

        $folderObj = new Folder($this->ffcHome);
        $configFiles = $folderObj->findRecursive('.*\.(' . $extensions . ')', true);

        $configFilesReturn = [];
        foreach ($configFiles as $k => $configFile) {
            $configFilesReturn[pathinfo($configFile, PATHINFO_BASENAME)] = $configFile;
        }

        return $configFilesReturn;
    }


    /**
     * Reformat data into the required format
     *
     * @param mixed $data
     * @param string $format raw|simplexml|domdocument|array|json
     * @return array|DOMDocument|SimpleXMLElement|string
     */
    private function _reformatRawData($data, string $format)
    {
        if ($format == 'raw') {
            return $data;
        } elseif ($format == 'simplexml') {
            $data = str_ireplace("utf-16", "utf-8", $data);
            $defaults = [
                'return' => 'simplexml',
                'loadEntities' => false,
                'readFile' => true,
                'parseHuge' => false,
            ];
            return Xml::build($data, $defaults);
        } elseif ($format == 'domdocument') {
            $data = str_ireplace("utf-16", "utf-8", $data);
            $defaults = [
                'return' => 'domdocument',
                'loadEntities' => false,
                'readFile' => true,
                'parseHuge' => false,
            ];
            return Xml::build($data, $defaults);
        } elseif ($format == 'array') {
            $data = str_ireplace("utf-16", "utf-8", $data);
            $defaults = [
                'return' => 'simplexml',
                'loadEntities' => false,
                'readFile' => true,
                'parseHuge' => false,
            ];
            return Xml::toArray(Xml::build($data, $defaults));
        } elseif ($format == 'json') {
            $data = str_ireplace("utf-16", "utf-8", $data);
            $defaults = [
                'return' => 'simplexml',
                'loadEntities' => false,
                'readFile' => true,
                'parseHuge' => false,
            ];
            return json_encode(Xml::toArray(Xml::build($data, $defaults)), JSON_PRETTY_PRINT);
        } else {
            return $data;
        }
    }


    /**
     * Parse a XLM file into an array
     *
     * @param string $licenceFileContents
     * @return array
     */
    private function _parseLicenseFile($licenceFileContents = '')
    {

        $licenceFileContents = str_replace("\r\n", '|', $licenceFileContents);
        $licenceFileContents = str_replace("\r", '|', $licenceFileContents);
        $licenceFileContents = str_replace("\n", '|', $licenceFileContents);

        $licenceFileContentsArray = explode('|', $licenceFileContents);

        $featureLines = [];
        foreach ($licenceFileContentsArray as $k => $line) {
            if (strpos($line, 'FEATURE ') === 0) {
                $featureLines[] = $line;
            }
        }

        $returnArray = [];
        foreach ($featureLines as $k => $featureLine) {
            $parts = explode(" ", $featureLine);

            foreach ($parts as $k2 => $part) {

                if ($k2 == 1) {
                    $returnArray[$k]['Feature'] = $part;
                }

                if ($k2 == 3) {
                    $returnArray[$k]['Version'] = $part;
                }

                if ($date = strtotime($part)) {
                    $returnArray[$k]['Expiry'] = Time::createFromTimestamp($date, TZ);
                }
            }
        }

        return $returnArray;
    }

    /**
     * See if there is an XPIF ticket associated with the PDL
     *
     * @param $pdlFile
     * @param null $failValue
     * @return null|string
     */
    public function findAssociatedXpifTicket($pdlFile, $failValue = null)
    {
        $pdlExtension = pathinfo($pdlFile, PATHINFO_EXTENSION);

        if (is_file($pdlFile . ".xpf")) {
            $xpifFullPath = $pdlFile . ".xpf";
        } elseif (is_file($pdlFile . ".xpif")) {
            $xpifFullPath = $pdlFile . ".xpf";
        } elseif (is_file(str_replace($pdlExtension, 'xpf', $pdlFile))) {
            $xpifFullPath = str_replace($pdlExtension, 'xpf', $pdlFile);
        } elseif (is_file(str_replace($pdlExtension, 'xpif', $pdlFile))) {
            $xpifFullPath = str_replace($pdlExtension, 'xpif', $pdlFile);
        } else {
            $xpifFullPath = $failValue;
        }

        return $xpifFullPath;
    }

    /**
     * @param $maxConfig
     * @return array
     */
    public function maxConfigToMaxHeaders($maxConfig)
    {
        $defaultColumns = [
            'FileNameColumn',
            'OrderIDColumn',
            'JobTypeColumn',
            'FolderNameColumn',
            'QuantityColumn',
            'GroupKeyColumn',
            'XPIFFileNameColumn',
            'PaperStockNameColumn',
            'PrinterDestinationColumn',
            'PartIDColumn'
        ];

        $compiledHeaders = [];

        //default columns
        foreach ($defaultColumns as $column) {
            if ($maxConfig[$column] > 0) {
                $compiledHeaders[str_replace("Column", '', $column)] = $maxConfig[$column];
            }
        }

        //custom columns
        if (!isset($maxConfig['CustomColumns']['CustomColumn'])) {
            $customColumnsToLoop = [];
        } elseif ($this->isSeqArr($maxConfig['CustomColumns']['CustomColumn'])) {
            $customColumnsToLoop = $maxConfig['CustomColumns']['CustomColumn'];
        } else {
            $customColumnsToLoop = [$maxConfig['CustomColumns']['CustomColumn']];
        }

        foreach ($customColumnsToLoop as $customColumn) {
            $compiledHeaders[str_replace("Column", '', $customColumn['ColumnName'])] = $customColumn['ColumnNumber'];
        }

        asort($compiledHeaders);
        return $compiledHeaders;
    }

    public function dataToMaxFile($tenantId = null, $hotFolderName = null, $dataRows = null)
    {
        $hotFolderInfo = $this->getTenantHotFolderDataByName($tenantId, $hotFolderName);
        $maxHeaders = $this->maxConfigToMaxHeaders($hotFolderInfo['MAXConfig']);

        //make base array, populate missing columns from defined MAX structure
        $baseArray = range(1, max($maxHeaders));
        $baseArray = array_combine(array_values($baseArray), array_values($baseArray));
        foreach ($maxHeaders as $maxHeaderKey => $maxHeaderValue) {
            $baseArray[$maxHeaderValue] = $maxHeaderKey;
        }
        $baseArray = array_flip($baseArray);
        foreach ($baseArray as $k => $v) {
            $baseArray[$k] = '';
        }

        //populate array
        $buildArray = [];
        $counter = 1;
        foreach ($dataRows as $dataRow) {
            $buildArray[$counter] = $baseArray;
            foreach ($dataRow as $key => $item) {
                if (isset($maxHeaders[$key])) {
                    $buildArray[$counter][$key] = $item;
                }
            }
            $counter++;
        }


        //convert to CSV
        if (isset($hotFolderInfo['MAXConfig']['ColumnDelimiter'])) {
            $columnDelimiter = $hotFolderInfo['MAXConfig']['ColumnDelimiter'];
        } else {
            $columnDelimiter = ',';
        }

        if (isset($hotFolderInfo['MAXConfig']['ProcessHeaderRow'])) {
            $processHeaderRow = $hotFolderInfo['MAXConfig']['ProcessHeaderRow'];
        } else {
            $processHeaderRow = false;
        }

        if (isset($hotFolderInfo['MAXConfig']['TextQualifier'])) {
            $textQualifier = $hotFolderInfo['MAXConfig']['TextQualifier'];
        } else {
            $textQualifier = '';
        }

        $csv = [];
        foreach ($buildArray as $row) {
            $csv[] = $textQualifier
                . implode($textQualifier . $columnDelimiter . $textQualifier, $row)
                . $textQualifier
                . "\r\n";
        }
        $csv = implode("", $csv);

        if ($processHeaderRow == false || strtolower($processHeaderRow) == 'false') {
            $header = $textQualifier
                . implode($textQualifier . $columnDelimiter . $textQualifier, array_keys($baseArray))
                . $textQualifier
                . "\r\n";
            $csv = $header . $csv;
        }

        return $csv;
    }

    /**
     * Check if an array is numerically indexed at the first level
     *
     * @param array $arr
     * @return bool
     */
    private function isSeqArr(array $arr)
    {
        //empty array
        if ([] === $arr) {
            return false;
        }

        //check keys
        if (array_keys($arr) == range(0, count($arr) - 1)) {
            $return = true;
        } else {
            $return = false;
        }

        return $return;
    }

}