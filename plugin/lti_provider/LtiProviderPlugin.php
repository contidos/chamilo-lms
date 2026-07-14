<?php
/* For license terms, see /license.txt */

use Chamilo\PluginBundle\Entity\LtiProvider\Platform;
use Chamilo\PluginBundle\Entity\LtiProvider\PlatformKey;
use Chamilo\PluginBundle\Entity\LtiProvider\Result;
use Chamilo\PluginBundle\Entity\LtiProvider\License;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Description of LtiProvider.
 *
 * @author Christian Beeznest <christian.fasanando@beeznest.com>
 */
class LtiProviderPlugin extends Plugin
{
    public const TABLE_PLATFORM = 'plugin_lti_provider_platform';
    public const LAUNCH_PATH = 'lti_provider/tool/start.php';
    public const LOGIN_PATH = 'lti_provider/tool/login.php';
    public const REDIRECT_PATH = 'lti_provider/tool/start.php';
    public const JWKS_URL = 'lti_provider/tool/jwks.php';

    public $isAdminPlugin = true;

    protected function __construct()
    {
        $version = '1.1';
        $author = 'Christian Beeznest';

        $message = Display::return_message($this->get_lang('Description'));

        $launchUrlHtml = '';
        $loginUrlHtml = '';
        $redirectUrlHtml = '';
        $jwksUrlHtml = '';

        if ($this->areTablesCreated()) {
            $publicKey = $this->getPublicKey();

            $pkHtml = $this->getSettingHtmlReadOnly(
                $this->get_lang('PublicKey'),
                'public_key',
                $publicKey
            );
            $launchUrlHtml = $this->getSettingHtmlReadOnly(
                $this->get_lang('LaunchUrl'),
                'launch_url',
                api_get_path(WEB_PLUGIN_PATH).self::LAUNCH_PATH
            );
            $loginUrlHtml = $this->getSettingHtmlReadOnly(
                $this->get_lang('LoginUrl'),
                'login_url',
                api_get_path(WEB_PLUGIN_PATH).self::LOGIN_PATH
            );
            $redirectUrlHtml = $this->getSettingHtmlReadOnly(
                $this->get_lang('RedirectUrl'),
                'redirect_url',
                api_get_path(WEB_PLUGIN_PATH).self::REDIRECT_PATH
            );
            $jwksUrlHtml = $this->getSettingHtmlReadOnly(
                $this->get_lang('KeySetUrlJwks'),
                'jwks_url',
                api_get_path(WEB_PLUGIN_PATH).self::JWKS_URL
            );
        } else {
            $pkHtml = $this->get_lang('GenerateKeyPairInfo');
        }

        $settings = [
            $message => 'html',
            'name' => 'hidden',
            $launchUrlHtml => 'html',
            $loginUrlHtml => 'html',
            $redirectUrlHtml => 'html',
            $jwksUrlHtml => 'html',
            $pkHtml => 'html',
            'enabled' => 'boolean',
        ];
        parent::__construct($version, $author, $settings);
    }

    /**
     * Get the value by default and readonly for the configuration html form.
     *
     * @param $label
     * @param $id
     * @param $value
     *
     * @return string
     */
    public function getSettingHtmlReadOnly($label, $id, $value)
    {
        $html = '<div class="form-group">
                    <label for="lti_provider_'.$id.'" class="col-sm-2 control-label">'
            .$label.'</label>
                    <div class="col-sm-8">
                        <pre>'.$value.'</pre>
                    </div>
                    <div class="col-sm-2"></div>
                    <input type="hidden" name="'.$id.'" value="'.$value.'" />
                </div>';

        return $html;
    }

    /**
     * Get a selectbox with quizzes in courses , used for a tool provider.
     *
     * @param null $clientId
     *
     * @return string
     */
    public function getQuizzesSelect($clientId = null)
    {
        $courses = CourseManager::get_courses_list();
        $toolProvider = $this->getToolProvider($clientId);
        $htmlcontent = '<div class="form-group select-tool" id="select-quiz">
            <label for="lti_provider_create_platform_kid" class="col-sm-2 control-label">'.$this->get_lang('ToolProvider').'</label>
            <div class="col-sm-8">
                <select name="tool_provider" class="sbox-tool" id="sbox-tool-quiz" disabled="disabled">';
        $htmlcontent .= '<option value="">-- '.$this->get_lang('SelectOneActivity').' --</option>';
        foreach ($courses as $course) {
            $courseInfo = api_get_course_info($course['code']);
            $optgroupLabel = "{$course['title']} : ".get_lang('Quizzes');
            $htmlcontent .= '<optgroup label="'.$optgroupLabel.'">';
            $exerciseList = ExerciseLib::get_all_exercises_for_course_id(
                $courseInfo,
                0,
                $course['id'],
                false
            );
            foreach ($exerciseList as $key => $exercise) {
                $selectValue = "{$course['code']}@@quiz-{$exercise['iid']}";
                $htmlcontent .= '<option value="'.$selectValue.'" '.($toolProvider == $selectValue ? ' selected="selected"' : '').'>'.Security::remove_XSS($exercise['title']).'</option>';
            }
            $htmlcontent .= '</optgroup>';
        }
        $htmlcontent .= "</select>";
        $htmlcontent .= '   </div>
                    <div class="col-sm-2"></div>
                    </div>';

        return $htmlcontent;
    }

    /**
     * Get a selectbox with quizzes in courses , used for a tool provider.
     *
     * @param null $clientId
     *
     * @return string
     */
    public function getLearnPathsSelect($clientId = null)
    {
        $courses = CourseManager::get_courses_list();
        $toolProvider = $this->getToolProvider($clientId);
        $htmlcontent = '<div class="form-group select-tool" id="select-lp" style="display:none">
            <label for="lti_provider_create_platform_kid" class="col-sm-2 control-label">'.$this->get_lang('ToolProvider').'</label>
            <div class="col-sm-8">
                <select name="tool_provider" class="sbox-tool" id="sbox-tool-lp" disabled="disabled">';
        $htmlcontent .= '<option value="">-- '.$this->get_lang('SelectOneActivity').' --</option>';
        foreach ($courses as $course) {
            $courseInfo = api_get_course_info($course['code']);
            $optgroupLabel = "{$course['title']} : ".get_lang('Learnpath');
            $htmlcontent .= '<optgroup label="'.$optgroupLabel.'">';

            $list = new LearnpathList(
                api_get_user_id(),
                $courseInfo
            );

            $flatList = $list->get_flat_list();
            foreach ($flatList as $id => $details) {
                $selectValue = "{$course['code']}@@lp-{$id}";
                $htmlcontent .= '<option value="'.$selectValue.'" '.($toolProvider == $selectValue ? ' selected="selected"' : '').'>'.Security::remove_XSS($details['lp_name']).'</option>';
            }
            $htmlcontent .= '</optgroup>';
        }
        $htmlcontent .= "</select>";
        $htmlcontent .= '   </div>
                    <div class="col-sm-2"></div>
                    </div>';

        return $htmlcontent;
    }

    public function getLearnPathsSessionSelect($clientId = null)
    {
        $sessions = SessionManager::get_sessions_list([],['name']);
        $toolProvider = $this->getToolProvider($clientId);
        $htmlcontent = '<div class="form-group select-tool" id="select-session" style="display:none">
            <label for="lti_provider_create_platform_kid" class="col-sm-2 control-label">'.$this->get_lang('ToolProvider').'</label>
            <div class="col-sm-8">
                <select name="tool_provider" class="sbox-tool" id="sbox-tool-session" disabled="disabled">';
        $htmlcontent .= '<option value="">-- '.$this->get_lang('SelectOneActivity').' --</option>';
        foreach ($sessions as $session) {
            $sessionInfo = api_get_session_info($session['id']);
            $courses = SessionManager::get_course_list_by_session_id($session['id']);

            foreach ($courses as $course) {
                $courseInfo = api_get_course_info($course['code']);

                $optgroupLabel = "{$sessionInfo['name']} ({$course['title']}) : ".get_lang('Learnpath')." (ID: {$session['id']})";
                $htmlcontent .= '<optgroup label="'.$optgroupLabel.'">';

                $list = new LearnpathList(
                    api_get_user_id(),
                    $courseInfo,
                    $session['id'],
                    null,
                    false,
                    null,
                    true,
                    true
                );

                $flatList = $list->get_flat_list();
                foreach ($flatList as $id => $details) {
                    $selectValue = "{$course['code']}@@lp-{$id}@@session-{$session['id']}";
                    $htmlcontent .= '<option value="'.$selectValue.'" '.($toolProvider == $selectValue ? ' selected="selected"' : '').'>'.Security::remove_XSS($details['lp_name']).'</option>';
                }
                $htmlcontent .= '</optgroup>';
            }
        }
        $htmlcontent .= "</select>";
        $htmlcontent .= '   </div>
                    <div class="col-sm-2"></div>
                    </div>';

        return $htmlcontent;
    }

    /**
     * Get the public key.
     */
    public function getPublicKey(): string
    {
        $publicKey = '';
        $platformKey = Database::getManager()
           ->getRepository('ChamiloPluginBundle:LtiProvider\PlatformKey')
           ->findOneBy([]);

        if ($platformKey) {
            $publicKey = $platformKey->getPublicKey();
        }

        return $publicKey;
    }

    /**
     * Get the first access date of a user in a tool.
     *
     * @param $courseCode
     * @param $toolId
     * @param $userId
     *
     * @return string
     */
    public function getUserFirstAccessOnToolLp($courseCode, $toolId, $userId)
    {
        $dql = "SELECT
                    a.startDate
                FROM  ChamiloPluginBundle:LtiProvider\Result a
                WHERE
                    a.courseCode = '$courseCode' AND
                    a.toolName = 'lp' AND
                    a.toolId = $toolId AND
                    a.userId = $userId
                ORDER BY a.startDate";
        $qb = Database::getManager()->createQuery($dql);
        $result = $qb->getArrayResult();

        $firstDate = '';
        if (isset($result[0])) {
            $startDate = $result[0]['startDate'];
            $firstDate = $startDate->format('Y-m-d H:i');
        }

        return $firstDate;
    }

    /**
     * Get the results of users in tools lti.
     *
     * @param $startDate
     * @param $endDate
     *
     * @return array
     */
    public function getToolLearnPathResult($startDate, $endDate)
    {
        $dql = "SELECT
                    a.issuer,
                    count(DISTINCT(a.userId)) as cnt
                FROM
                    ChamiloPluginBundle:LtiProvider\Result a
                WHERE
                    a.toolName = 'lp' AND
                    a.startDate BETWEEN '$startDate' AND '$endDate'
                GROUP BY a.issuer";
        $qb = Database::getManager()->createQuery($dql);
        $issuersValues = $qb->getResult();

        $result = [];
        if (!empty($issuersValues)) {
            foreach ($issuersValues as $issuerValue) {
                $issuer = $issuerValue['issuer'];
                $dqlLp = "SELECT
                    a.toolId,
                    a.userId,
                    a.courseCode
                FROM
                    ChamiloPluginBundle:LtiProvider\Result a
                WHERE
                    a.toolName = 'lp' AND
                    a.startDate BETWEEN '$startDate' AND '$endDate' AND
                    a.issuer = '".$issuer."'
                GROUP BY a.toolId, a.userId";
                $qbLp = Database::getManager()->createQuery($dqlLp);
                $lpValues = $qbLp->getResult();

                $lps = [];
                foreach ($lpValues as $lp) {
                    $uinfo = api_get_user_info($lp['userId']);
                    $firstAccess = self::getUserFirstAccessOnToolLp($lp['courseCode'], $lp['toolId'], $lp['userId']);
                    $lps[$lp['toolId']]['users'][$lp['userId']] = [
                        'firstname' => $uinfo['firstname'],
                        'lastname' => $uinfo['lastname'],
                        'first_access' => $firstAccess,
                    ];
                }
                $result[] = [
                    'issuer' => $issuer,
                    'count_iss_users' => $issuerValue['cnt'],
                    'learnpaths' => $lps,
                ];
            }
        }

        return $result;
    }

    /**
     * Get the tool provider.
     */
    public function getToolProvider($clientId): string
    {
        $toolProvider = '';
        $platform = Database::getManager()
            ->getRepository('ChamiloPluginBundle:LtiProvider\Platform')
            ->findOneBy(['clientId' => $clientId]);

        if ($platform) {
            $toolProvider = $platform->getToolProvider();
        }

        return $toolProvider;
    }

    public function getToolProviderVars_old($clientId): array
    {
        $toolProvider = $this->getToolProvider($clientId);
        list($courseCode, $tool) = explode('@@', $toolProvider);
        list($toolName, $toolId) = explode('-', $tool);
        $vars = ['courseCode' => $courseCode, 'toolName' => $toolName, 'toolId' => $toolId];

        return $vars;
    }

    public function getToolProviderVars($clientId): array
    {
        $toolProvider = $this->getToolProvider($clientId);
        $parts = explode('@@', $toolProvider);

        $courseCode = $parts[0];
        $tool = $parts[1];
        $sessionId = $parts[2] ?? 'session-0';

        list($toolName, $toolId) = explode('-', $tool);

        $sessionId = str_replace('session-', '', $sessionId);

        $vars = [
            'courseCode' => $courseCode,
            'toolName' => $toolName,
            'toolId' => $toolId,
            'sessionId' => $sessionId,
        ];

        return $vars;
    }

    public function getStatsResult($toolId, $startDate, $endDate)
    {
        $dateFilter = '';
        if (!empty($startDate) && !empty($endDate)) {
            $dateFilter = " AND plpr.start_date BETWEEN '$startDate' AND '$endDate' ";
        }

        $toolIdFilter = '';
        if (!empty($toolId) || $toolId != "0") {
            $toolIdFilter = " AND plpp.id = $toolId ";
        }

        $sql = "SELECT plpp.id, CONCAT(plpp.name, ' (', plpp.client_id, ')') as lti_name, u.username, u.firstname, u.lastname, u.email,
            CONCAT(c.title, ' (', c.code, ')') as course, CONCAT(s.name, ' (', s.id, ')') as session, min(plpr.start_date) as first_date, max(plpr.start_date) as last_date
            FROM plugin_lti_provider_platform plpp
                LEFT JOIN plugin_lti_provider_result plpr on plpp.client_id = plpr.client_id
                LEFT JOIN user u on plpr.user_id = u.id
                LEFT JOIN course c on plpr.course_code = c.code
                LEFT JOIN session s on plpr.session_id = s.id
            WHERE 1=1 $toolIdFilter $dateFilter
            GROUP BY plpp.id, plpp.name, u.username, u.firstname, u.lastname, u.email, c.code, c.title, s.id, s.name";

        $result = Database::query($sql);

        $data = [];

        while ($res = Database::fetch_array($result, 'ASSOC')) {
            $data[]= $res;
        }

        return $data;
    }

    public function getTools() {
        $sql = "SELECT id, CONCAT(name, ' (',client_id, ')') as tool FROM plugin_lti_provider_platform;";

        $result = Database::query($sql);

        $data = [];

        while ($res = Database::fetch_array($result, 'ASSOC')) {
            $data[]= $res;
        }

        return $data;
    }


    public static function printLtiLearningPath()
    {
        $content = Display::page_header(get_lang('LearningPathLTI'));
        $form = new FormValidator('frm_lti_tool_lp', 'get');

        $tools = self::getTools();

        $tool_select_list = [];
        $tool_select_list[0] = ' -- '.get_lang('Select').' --';
        foreach ($tools as $item) {
            $tool_select_list[$item['id']] = $item['tool'];
        }

        $form->addSelect(
            'tool_id',
            'LTI',
            $tool_select_list,
            ['id' => 'filter_1']
        );

        $form->addDateRangePicker(
            'daterange',
            get_lang('DateRange'),
            true,
            ['format' => 'YYYY-MM-DD', 'timePicker' => 'false', 'validate_format' => 'Y-m-d']
        );
        $form->addHidden('report', 'lti_tool_lp');
        $form->addButtonFilter(get_lang('Search'));

        if ($form->validate()) {
            $values = $form->exportValues();

            $toolId = $values['tool_id'];
            $startDate = $values['daterange_start'];
            $endDate = $values['daterange_end'];

            $content .= '<button class="btn btn-success" style="margin-bottom: 10px;" onclick="exportarExcel('.$toolId.', \''.$startDate.'\', \''.$endDate.'\')">Exportar a Excel</button>';

            $content .= self::getStatsTable($toolId, $startDate, $endDate);
        }

        $content .= $form->returnForm();

        return $content;
    }

    public static function getStatsTable($toolId, $startDate, $endDate)
    {
        $data = self::getLtiLearningPathByDate($toolId, $startDate, $endDate);
        $table = new HTML_Table(['class' => 'table table-bordered data_table']);
        $table->setHeaderContents(0, 0, get_lang('LTI'));
        $table->setHeaderContents(0, 1, get_lang('UserName'));
        $table->setHeaderContents(0, 2, get_lang('FirstName'));
        $table->setHeaderContents(0, 3, get_lang('LastName'));
        $table->setHeaderContents(0, 4, get_lang('Email'));
        $table->setHeaderContents(0, 5, get_lang('Course'));
        $table->setHeaderContents(0, 6, "Sesion");
        $table->setHeaderContents(0, 7, get_lang('Prm. acceso'));
        $table->setHeaderContents(0, 8, get_lang('Ult. acceso'));
        $i = 1;
        foreach ($data as $item) {
            $table->setCellContents($i, 0, $item['lti_name']);
            $table->setCellContents($i, 1, $item['username']);
            $table->setCellContents($i, 2, $item['firstname']);
            $table->setCellContents($i, 3, $item['lastname']);
            $table->setCellContents($i, 4, $item['email']);
            $table->setCellContents($i, 5, $item['course']);
            $table->setCellContents($i, 6, $item['session']);
            $table->setCellContents($i, 7, $item['first_date']);
            $table->setCellContents($i, 8, $item['last_date']);
            $i++;
        }
        return $table->toHtml();
    }

    /**
     * It gets lti learnpath results by date.
     *
     * @param string $startDate Start date in YYYY-MM-DD format
     * @param string $endDate   End date in YYYY-MM-DD format
     */
    private static function getLtiLearningPathByDate(int $toolId, string $startDate, string $endDate): array
    {
        /** @var DateTime $startDate */
        $startDate = api_get_utc_datetime("$startDate 00:00:00");
        /** @var DateTime $endDate */
        $endDate = api_get_utc_datetime("$endDate 23:59:59");

        if (empty($startDate) || empty($endDate)) {
            return [];
        }

        require_once api_get_path(SYS_PLUGIN_PATH).'lti_provider/LtiProviderPlugin.php';

        $plugin = LtiProviderPlugin::create();

        $result = $plugin->getStatsResult($toolId,$startDate, $endDate);

        return $result;
    }

    /**
     * Get the class instance.
     *
     * @staticvar LtiProviderPlugin $result
     */
    public static function create(): LtiProviderPlugin
    {
        static $result = null;

        return $result ?: $result = new self();
    }

    /**
     * Check whether the current user is a teacher in this context.
     */
    public static function isInstructor()
    {
        api_is_allowed_to_edit(false, true);
    }

    /**
     * Get the plugin directory name.
     */
    public function get_name(): string
    {
        return 'lti_provider';
    }

    /**
     * Install the plugin. Set the database up.
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function install()
    {
        $em = Database::getManager();

        if ($em->getConnection()->getSchemaManager()->tablesExist([self::TABLE_PLATFORM])) {
            return;
        }

        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema(
            [
                $em->getClassMetadata(Platform::class),
                $em->getClassMetadata(PlatformKey::class),
                $em->getClassMetadata(Result::class),
                $em->getClassMetadata(License::class),
            ]
        );
    }

    /**
     * Save configuration for plugin.
     *
     * Generate a new key pair for platform when enabling plugin.
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function performActionsAfterConfigure()
    {
        $em = Database::getManager();

        /** @var PlatformKey $platformKey */
        $platformKey = $em
            ->getRepository('ChamiloPluginBundle:LtiProvider\PlatformKey')
            ->findOneBy([]);

        if ($this->get('enabled') === 'true') {
            if (!$platformKey) {
                $platformKey = new PlatformKey();
            }

            $keyPair = self::generatePlatformKeys();

            $platformKey->setKid($keyPair['kid']);
            $platformKey->publicKey = $keyPair['public'];
            $platformKey->setPrivateKey($keyPair['private']);

            $em->persist($platformKey);
        } else {
            if ($platformKey) {
                $em->remove($platformKey);
            }
        }

        $em->flush();

        return $this;
    }

    /**
     * Unistall plugin. Clear the database.
     */
    public function uninstall()
    {
        $em = Database::getManager();

        if (!$em->getConnection()->getSchemaManager()->tablesExist([self::TABLE_PLATFORM])) {
            return;
        }

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema(
            [
                $em->getClassMetadata(Platform::class),
                $em->getClassMetadata(PlatformKey::class),
                $em->getClassMetadata(Result::class),
            ]
        );
    }

    public function trimParams(array &$params)
    {
        foreach ($params as $key => $value) {
            $newValue = preg_replace('/\s+/', ' ', $value);
            $params[$key] = trim($newValue);
        }
    }

    public function saveResult($values, $ltiLaunchId = null)
    {
        $em = Database::getManager();
        if (!empty($ltiLaunchId)) {
            $repo = $em->getRepository(Result::class);

            /** @var Result $objResult */
            $objResult = $repo->findOneBy(
                [
                    'ltiLaunchId' => $ltiLaunchId,
                ]
            );
            if ($objResult) {
                $objResult->setScore($values['score']);
                $objResult->setProgress($values['progress']);
                $objResult->setDuration($values['duration']);
                $em->persist($objResult);
                $em->flush();

                return $objResult->getId();
            }
        } else {
            $objResult = new Result();
            $objResult
                ->setIssuer($values['issuer'])
                ->setUserId($values['user_id'])
                ->setClientUId($values['client_uid'])
                ->setCourseCode($values['course_code'])
                ->setToolId($values['tool_id'])
                ->setToolName($values['tool_name'])
                ->setScore(0)
                ->setProgress(0)
                ->setDuration(0)
                ->setStartDate(new DateTime())
                ->setUserIp(api_get_real_ip())
                ->setLtiLaunchId($values['lti_launch_id'])
                ->setSessionId($values['session_id'])
                ->setClientId($values['client_id'])
            ;
            $em->persist($objResult);
            $em->flush();

            return $objResult->getId();
        }

        return false;
    }

    private function areTablesCreated(): bool
    {
        $entityManager = Database::getManager();
        $connection = $entityManager->getConnection();

        return $connection->getSchemaManager()->tablesExist(self::TABLE_PLATFORM);
    }

    /**
     * Generate a key pair and key id for the platform.
     *
     * Return a associative array like ['kid' => '...', 'private' => '...', 'public' => '...'].
     */
    private static function generatePlatformKeys(): array
    {
        // Create the private and public key
        $res = openssl_pkey_new(
            [
                'digest_alg' => 'sha256',
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );

        // Extract the private key from $res to $privateKey
        $privateKey = '';
        openssl_pkey_export($res, $privateKey);

        // Extract the public key from $res to $publicKey
        $publicKey = openssl_pkey_get_details($res);

        return [
            'kid' => bin2hex(openssl_random_pseudo_bytes(10)),
            'private' => $privateKey,
            'public' => $publicKey["key"],
        ];
    }

    /**
     * Get a SimpleXMLElement object with the request received on php://input.
     *
     * @throws Exception
     */
    private function getRequestXmlElement(): ?SimpleXMLElement
    {
        $request = file_get_contents("php://input");

        if (empty($request)) {
            return null;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($request, SimpleXMLElement::class, LIBXML_NONET);

        return $xml !== false ? $xml : null;
    }

    /**
     * Check if there are available licenses for a client platform.
     *
     * @param int $clientId The client ID to check
     * @return bool True if there are available licenses, false otherwise
     */
    public function hasAvailableLicenses(string $clientId): bool
    {
        $platform = Database::getManager()
            ->getRepository('ChamiloPluginBundle:LtiProvider\Platform')
            ->findOneBy([
                'clientId' => $clientId
            ]);

        if (!$platform) {
            return false;
        }

        $totalLicenses = $platform->getTotalLicenses();
        if ($totalLicenses === 0) {
            return true; // Unlimited licenses
        }

        $usedLicenses = Database::getManager()
            ->createQuery('
                SELECT COUNT(l.id) 
                FROM ChamiloPluginBundle:LtiProvider\License l 
                WHERE l.clientId = :clientId AND l.expired = false
            ')
            ->setParameter('clientId', $clientId)
            ->getSingleScalarResult();

        return $usedLicenses < $totalLicenses;
    }

    /**
     * Check if a user has an active license for a platform.
     *
     * @param int $userId The user ID to check
     * @param string $clientId The platform client ID to check
     * @return bool True if the user has an active license, false otherwise
     */
    public function hasUserLicense(int $userId, string $clientId): bool
    {
        $license = Database::getManager()
            ->getRepository('ChamiloPluginBundle:LtiProvider\License')
            ->findOneBy([
                'userId' => $userId,
                'clientId' => $clientId,
                'expired' => false
            ]);

        return $license !== null;
    }

    /**
     * Add a new license for a user and platform.
     *
     * @param int $userId The user ID
     * @param string $clientId The platform client ID
     * @return bool True if the license was added successfully, false otherwise
     */
    public function addUserLicense(int $userId, string $clientId): bool
    {
        if (!$this->hasAvailableLicenses($clientId)) {
            return false;
        }

        if ($this->hasUserLicense($userId, $clientId)) {
            return false;
        }

        try {
            $em = Database::getManager();
            
            // Get the platform
            $platform = $em->getRepository('ChamiloPluginBundle:LtiProvider\Platform')
                ->findOneBy(['clientId' => $clientId]);

            if (!$platform) {
                return false;
            }

            // Create and persist the new license
            $license = new License();
            $license
                ->setUserId($userId)
                ->setClientId($clientId)
                ->setExpeditionDate(new \DateTime())
                ->setExpired(false);

            $em->persist($license);

            // Update available licenses count
            $currentAvailable = $platform->getAvailableLicenses();
            $platform->setAvailableLicenses($currentAvailable - 1);

            $em->persist($platform);
            $em->flush();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deactivate a user's license for a platform.
     *
     * @param int $userLicense The user license ID
     * @param string $clientId The platform client ID
     * @return bool True if the license was deactivated successfully, false otherwise
     */
    public function deactivateUserLicense(int $userLicense, string $clientId): bool
    {
        try {
            $em = Database::getManager();
            
            // Get the license
            $license = $em->getRepository('ChamiloPluginBundle:LtiProvider\License')
                ->findOneBy([
                    'id' => $userLicense,
                    'clientId' => $clientId
                ]);

            if (!$license) {
                return false;
            }

            // Get the platform
            $platform = $em->getRepository('ChamiloPluginBundle:LtiProvider\Platform')
                ->findOneBy(['clientId' => $clientId]);

            if (!$platform) {
                return false;
            }

            // Deactivate the license
            $license->setExpired(true);
            $em->persist($license);

            // Update available licenses count
            $currentAvailable = $platform->getAvailableLicenses();
            $platform->setAvailableLicenses($currentAvailable + 1);

            $em->persist($platform);
            $em->flush();
            
            return true;
        } catch (\Exception $e) {
            error_log('Error deactivating license: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a platform is limited by licenses.
     *
     * @param string $clientId The platform client ID to check
     * @return bool True if the platform is limited by licenses (totalLicenses > 0), false otherwise
     */
    public function isPlatformLicenseLimited(string $clientId): bool
    {
        $platform = Database::getManager()
            ->getRepository('ChamiloPluginBundle:LtiProvider\Platform')
            ->findOneBy([
                'clientId' => $clientId
            ]);

        if (!$platform) {
            error_log('Platform not found: ' . $clientId);
            return false;
        }

        return $platform->getTotalLicenses() > 0;
    }

    /**
     * Get licenses data for a platform.
     *
     * @param int $from Starting index
     * @param int $numberOfItems Number of items to return
     * @param int $column Column to sort by
     * @param string $direction Sort direction (ASC/DESC)
     * @param string $clientId The platform client ID
     * @return array Array of license data
     */
    public function getLicensesData(int $from, int $numberOfItems, int $column, string $direction, string $clientId): array
    {
        $em = Database::getManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('l')
            ->from('ChamiloPluginBundle:LtiProvider\License', 'l')
            ->where('l.clientId = :clientId')
            ->setParameter('clientId', $clientId)
            ->orderBy('l.expeditionDate', $direction)
            ->setFirstResult($from)
            ->setMaxResults($numberOfItems);

        $licenses = $qb->getQuery()->getResult();
        $data = [];

        foreach ($licenses as $license) {
            $userInfo = api_get_user_info($license->getUserId());
            $expeditionDate = $license->getExpeditionDate();
            
            $data[] = [
                $license->getId(),
                $userInfo['complete_name'],
                $userInfo['email'],
                $expeditionDate ? $expeditionDate->format('Y-m-d H:i:s') : '',
                $license->isExpired()
            ];
        }

        return $data;
    }

    /**
     * Get total number of licenses for a platform.
     *
     * @param string $clientId The platform client ID
     * @return int Total number of licenses
     */
    public function getTotalNumberOfLicenses(string $clientId): int
    {
        $em = Database::getManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('COUNT(l.id)')
            ->from('ChamiloPluginBundle:LtiProvider\License', 'l')
            ->where('l.clientId = :clientId')
            ->setParameter('clientId', $clientId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
