<?php

/**
 * Contao module om_searchkeys
 * 
 * Class ModuleOmSearchKeys
 * 
 * @copyright OMOS.de 2015<http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @package   om_searchkeys
 */

class ModuleOmSearchKeys extends BackendModule
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'om_searchkeys';


    /**
     * Generate module
     */
    protected function compile()
    {
        // prepare
        $this->loadLanguageFile('om_searchkeys');
        $this->import('Database');
        $this->import('BackendUser','User');


        // empty table?
        if ($_POST['FORM_SUBMIT'] == 'emptyTable')
        {
            $this->emptyTable();
            unset($_POST['FORM_SUBMIT']);
        }
        // export
        if ($this->Input->post('FORM_SUBMIT') == 'export')
        {
            $this->export();
            unset($_POST['FORM_SUBMIT']);
        }
    
        // generate output
        $this->Template->button     = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $this->Template->title      = specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
        $this->Template->url        = $this->Environment->base . $this->Environment->request;

        $this->generateOutput();
    }


    /**
     * empty table tl_om_searchkeys
     */
    public function emptyTable()
    {
        $this->Database->prepare("TRUNCATE TABLE tl_om_searchkeys;")->execute();
    }
  
  
    /**
     * export to csv
     */
    public function export()
    {
        $this->import('Database');

        //IE or other?
        $log_version ='';
        $HTTP_USER_AGENT = getenv("HTTP_USER_AGENT");

        if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version)) {
            $this->BrowserAgent = 'IE';
        } else {
            $this->BrowserAgent = 'NOIE';
        }

        // send header
        header('Content-Type: text/comma-separated-values');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="SearchKeys-' . date('d.m.Y') . '.utf8.csv"');
        if ($this->BrowserAgent == 'IE') {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Pragma: no-cache');
        }

        // send data
        $objSearchKeys = $this->Database->prepare("SELECT FROM_UNIXTIME(tstamp),searchkey,results,relevance FROM tl_om_searchkeys ORDER BY tstamp DESC")->execute();
        if ($objSearchKeys->numRows) {
            $arrSearchKeys = $objSearchKeys->fetchAllAssoc();
        }

        $out = fopen('php://output', 'w');
        fputcsv($out, array('Zeit', 'Keyword', 'Ergebnisse', 'Relevance (Durchschnitt)'), ',');
        foreach ($arrSearchKeys as $value) {
            fputcsv($out, $value, ',');
        }

        fclose($out);
        exit;
      }
  
  
    /**
     * generate Output
     */
    public function generateOutput()
    {
        // check startpoint
        if ($this->Input->post('FORM_SUBMIT') == 'rootPages' && $this->Input->post('root') != 0)
        {
            $strWhere = ' WHERE rootPage=' . $this->Input->post('root');
            $strWhereLatest = ' WHERE s.rootPage=' . $this->Input->post('root');
            $this->Template->startpoint = $this->Input->post('root');
        }

        // get Top
            $objTop = $this->Database->prepare("SELECT searchkey, COUNT(*) AS count FROM tl_om_searchkeys" . $strWhere ." GROUP BY searchkey ORDER BY COUNT(*) DESC")->limit(20)->execute();
        if ($objTop->numRows)
        {
            $arrTop = $objTop->fetchAllAssoc();
        }

        // get all backenduser
        $objUser = $this->Database->prepare("SELECT id,name,username FROM tl_user where disable<>1")->execute();
        while ($objUser->next())
        {
            $arrUser[$objUser->id] = $objUser->row();
        }

        // get Latest
        $objLast = $this->Database->prepare("SELECT s.searchkey,s.tstamp,s.member,s.results,s.relevance,m.firstname,m.lastname,m.username,s.user FROM tl_om_searchkeys as s LEFT JOIN tl_member as m ON s.member=m.id" . $strWhereLatest . " ORDER BY s.tstamp DESC")->limit(30)->execute();
        if ($objLast->numRows)
        {
            $arrLast = $objLast->fetchAllAssoc();

            foreach ($arrLast as $key=>$value) {
                if ($value['member'] > 0)
                {
                    $arrLast[$key]['link'] = $this->Environment->base . 'contao/main.php?do=member&act=edit&id=' . $value['member'];
                    $arrLast[$key]['icon'] = $this->Environment->base . 'system/modules/om_searchkeys/html/member.gif';
                } elseif ($value['user'] > 0) {
                    $arrLast[$key]['link'] = $this->Environment->base . 'contao/main.php?do=user&act=edit&id=' . $value['user'];
                    $arrLast[$key]['icon'] = $this->Environment->base . 'system/modules/om_searchkeys/html/user.gif';
                } else {
                    $arrLast[$key]['icon'] = $this->Environment->base . 'system/modules/om_searchkeys/html/member_.gif';
                }
            }
        }
    
        // get statistics
        $objTotal   = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_om_searchkeys")->limit(1)->execute();
        $objMonthly = $this->Database->prepare("SELECT ROUND(AVG(count)) AS monthly FROM (SELECT COUNT(*) AS count FROM tl_om_searchkeys" . $strWhere ." GROUP BY MONTH(FROM_UNIXTIME(tstamp))) as temp")->limit(1)->execute();
        $objWeekly  = $this->Database->prepare("SELECT ROUND(AVG(count)) AS weekly FROM (SELECT COUNT(*) AS count FROM tl_om_searchkeys" . $strWhere ." GROUP BY WEEK(FROM_UNIXTIME(tstamp))) as temp")->limit(1)->execute();
        $objDaily   = $this->Database->prepare("SELECT ROUND(AVG(count)) AS daily FROM (SELECT COUNT(*) AS count FROM tl_om_searchkeys" . $strWhere ." GROUP BY DAY(FROM_UNIXTIME(tstamp))) as temp")->limit(1)->execute();

        // get all root pages
        $arrRootPages = $this->Database->prepare("SELECT id,title FROM tl_page WHERE pid=0")->execute()->fetchAllAssoc();

        // set template vars
        $this->Template->rootPages    = $arrRootPages;
        $this->Template->user         = $arrUser;
        $this->Template->top          = $arrTop;
        $this->Template->last         = $arrLast;
        $this->Template->access       = ($this->User->isAdmin || $this->User->hasAccess('member', 'modules'));
        $this->Template->total        = ($objTotal->numRows) ? $objTotal->total : 0;
        $this->Template->monthly      = ($objMonthly->numRows) ? $objMonthly->monthly : 0;
        $this->Template->weekly       = ($objWeekly->numRows) ? $objWeekly->weekly : 0;
        $this->Template->daily        = ($objDaily->numRows) ? $objDaily->daily : 0;
        $this->Template->empty        = $GLOBALS['TL_LANG']['om_searchkeys']['empty'];
        $this->Template->root         = $GLOBALS['TL_LANG']['om_searchkeys']['root'];
        $this->Template->allRoots     = $GLOBALS['TL_LANG']['om_searchkeys']['allRoots'];
        $this->Template->emptyTable   = $GLOBALS['TL_LANG']['om_searchkeys']['emptyTable'];
        $this->Template->emptyConfirm = $GLOBALS['TL_LANG']['om_searchkeys']['emptyConfirm'];
        $this->Template->export       = $GLOBALS['TL_LANG']['om_searchkeys']['export'];
        $this->Template->lastQueries  = $GLOBALS['TL_LANG']['om_searchkeys']['lastQueries'];
        $this->Template->searchterm   = $GLOBALS['TL_LANG']['om_searchkeys']['searchterm'];
        $this->Template->result       = $GLOBALS['TL_LANG']['om_searchkeys']['result'];
        $this->Template->datetime     = $GLOBALS['TL_LANG']['om_searchkeys']['datetime'];
        $this->Template->member       = $GLOBALS['TL_LANG']['om_searchkeys']['member'];
        $this->Template->statistics   = $GLOBALS['TL_LANG']['om_searchkeys']['statistics'];
        $this->Template->selection    = $GLOBALS['TL_LANG']['om_searchkeys']['selection'];
        $this->Template->amount       = $GLOBALS['TL_LANG']['om_searchkeys']['amount'];
        $this->Template->top20        = $GLOBALS['TL_LANG']['om_searchkeys']['top20'];
        $this->Template->txtTotal     = $GLOBALS['TL_LANG']['om_searchkeys']['txtTotal'];
        $this->Template->txtMonthly   = $GLOBALS['TL_LANG']['om_searchkeys']['txtMonthly'];
        $this->Template->txtWeekly    = $GLOBALS['TL_LANG']['om_searchkeys']['txtWeekly'];
        $this->Template->txtDaily     = $GLOBALS['TL_LANG']['om_searchkeys']['txtDaily'];
    }
  
   
    /**
     * Split the current request into fragments, strip the URL suffix, recreate the $_GET array and return the page ID
     * @return mixed
     */
    protected function getPageIdFromUrl()
    {
        if ($GLOBALS['TL_CONFIG']['disableAlias'])
        {
            return is_numeric($this->Input->get('id')) ? $this->Input->get('id') : null;
        }

        if (!strlen($this->Environment->request))
        {
            return null;
        }

        $strRequest = preg_replace('/\?.*$/i', '', $this->Environment->request);
        $strRequest = preg_replace('/' . preg_quote($GLOBALS['TL_CONFIG']['urlSuffix'], '/') . '$/i', '', $strRequest);

        $arrFragments = explode('/', $strRequest);

        // Skip index.php
        if (strtolower($arrFragments[0]) == 'index.php')
        {
            array_shift($arrFragments);
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl']))
        {
            foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback)
            {
                $this->import($callback[0]);
                $arrFragments = $this->$callback[0]->$callback[1]($arrFragments);
            }
        }

        // Add fragments to $_GET array
        for ($i=1; $i<count($arrFragments); $i+=2)
        {
            $_GET[urldecode($arrFragments[$i])] = urldecode($arrFragments[$i+1]);
        }

        return strlen($arrFragments[0]) ? urldecode($arrFragments[0]) : null;
        }
    }


/**
 * Contao module om_searchkeys
 * 
 * Class format
 * 
 * @copyright OMOS.de 2015 <http://www.omos.de>
 * @author    René Fehrmann <rene.fehrmann@omos.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @package   om_searchkeys
 */
class Format {
    static public function arr_to_csv_line($arr) {
        $line = array();
        foreach ($arr as $v) {
            $line[] = is_array($v) ? self::arr_to_csv_line($v) : '"' . str_replace('"', '""', $v) . '"';
        }
        return implode(",", $line);
    }

    static public function arr_to_csv($arr) {
        $lines = array();
        foreach ($arr as $v) {
            $lines[] = self::arr_to_csv_line($v);
        }
        return implode("\n", $lines);
    }
}
