<?php

class C2_SmartyMailer
{
    /**
     * E-mailを送信する
     */
    public function send($mailTo, $subject, $template, $assigns = array(), $view = null)
    {
        if (!file_exists(VIEW_DIR . '/' . $template)) {
            throw new Exception('Not exists template. template=' . VIEW_DIR . '/' . $template);
        }
        if ($view === null) {
            require_once LAMP_DIR . '/Smarty.class.php';
            $view = new Lamp_Template_Smarty();
    
            // Translation
            require_once MULTILINGUAL_LIB_DIR . '/Translator/Smarty/SmartyTranslationPrefilter.class.php';
            $prefilter = new SmartyTranslationPrefilter(UserLangHandler::getMyRealCode());
            $view->smarty->register_prefilter(array(&$prefilter, 'filter'));
        }
        foreach ($assigns as $key => $value) {
            $view->assign($key, $value, false);
        }
        $view->setTemplate($template);

        require_once LAMP_DIR . '/Mailer.class.php';
        try {
            $mailer = new Lamp_Mailer();
            $mailer->addHeader('X-System-Form', '1');
            $mailer->addHeader('X-Form-SiteURL', FQDN);
            $mailer->setFrom(SYSTEM_MAIL);
            $mailer->setFromName(PUBLISHER_NAME);
            $mailer->addTo($mailTo);
            $mailer->setSubject($subject);
            $mailer->setBody($view->fetch());
            $mailer->send();
            CPOS_Logger::info("Send e-mail to $mailTo. subject=$subject");
        } catch (Exception $e) {
            CPOS_Logger::error('Failed to send mail. mailTo=' . $mailTo . ' subject=' . $subject . ' body=' . $view->fetch() . ' assigns=' . dumpVar($assigns) . ' exception=' . $e->getMessage());
            return false;
        }
        return true;
    }
}