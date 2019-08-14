<?php
/**
 * @author    Marcin Frymark
 * @email     contact@alekseon.com
 * @company   Alekseon
 * @website   www.alekseon.com
 */
class Alekseon_MailTransport_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    public function getMailTransportConfig($configField, $storeId)
    {
        return Mage::getStoreConfig('alekseon_mailTransport/general/' . $configField, $storeId);
    }

    public function getMail($storeId = null)
    {
        if (is_null($this->_mail)) {
            $this->_mail = parent::getMail();
            $transport = $this->_getTransport($storeId);
            if ($transport) {
                $this->_mail->setDefaultTransport($transport);
            }
        }
        return $this->_mail;
    }
    
    protected function _getTransport($storeId = null)
    {
        $transport = null;
        $transportType = $this->getMailTransportConfig('type', $storeId);
        switch($transportType) {
            case Alekseon_MailTransport_Model_System_Config_Source_MailTransportTypes::MAIL_TARNSPORT_TYPE_SMTP:
                $transport = $this->_getSmtpTransport($storeId);
                break;
            case Alekseon_MailTransport_Model_System_Config_Source_MailTransportTypes::MAIL_TARNSPORT_TYPE_FILE:
                $transport= $this->_getFileTranport($storeId);
                break;
        }
        
        return $transport;
    }
    
    protected function _getSmtpTransport($storeId = null)
    {
        $host = $this->getMailTransportConfig('smtp_host', $storeId);
        $config = array(
            'username' => $this->getMailTransportConfig('smtp_user', $storeId),
            'password' => $this->getMailTransportConfig('smtp_password', $storeId),
            'port'     => $this->getMailTransportConfig('smtp_port', $storeId),
            'auth'     => $this->getMailTransportConfig('smtp_auth', $storeId),
        );

        $ssl = $this->getMailTransportConfig('smtp_ssl', $storeId);
        if ($ssl) {
            $config['ssl'] = $ssl;
        }

        $transport = new Zend_Mail_Transport_Smtp($host, $config);
        return $transport;
    }
    
    protected function _getFileTranport($storeId = null)
    {
        $path = $this->getMailTransportConfig('file_path', $storeId);
        if ($path) {
            $path = implode(DS, explode('/', $path));
            $path = implode(DS, explode('\\', $path));
            $path = Mage::getBaseDir() . DS . $path;
        }
    
        $config = array(
            'path' => $path,
            //'callback' =>
        );
        $transport = new Zend_Mail_Transport_File($config);
        return $transport;
    }
    
    public function send($email, $name = null, array $variables = array())
    {
        if (isset($variables['store'])) {
            $store = $variables['store'];
            $this->getMail($store->getId());
        }
        return parent::send($email, $name, $variables);
    }
}