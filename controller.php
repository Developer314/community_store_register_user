<?php

namespace Concrete\Package\CommunityStoreRegisterUser;

use Package;
use Events;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Core;
use Config;
defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends Package
{
    protected $pkgHandle = 'community_store_register_user';
    protected $appVersionRequired = '5.7.0';
    protected $pkgVersion = '0.0.2';
    protected $pkgDescription = "Community Store Register User";
    protected $pkgName = "Community Store Register User";

   

    public function install()
    {
        $pkg = parent::install();
    }

    public function upgrade()
    {
        parent::upgrade();
       
    }
    
    function password_generate($chars) 
	{
	  $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
	  return substr(str_shuffle($data), 0, $chars);
	}
  


    public function on_start()
    {
        Events::addListener('on_community_store_order', function ($event) {
            $order = $event->getOrder();
            $email = $order->getAttribute("email")?$order->getAttribute("email"):'';
	       
	       $userExists = \UserInfo::getByEmail($email);
	        
	        if(!is_object($userExists)){
	        
	        	if($order->getCustomerID()==0){
	            
	            $userService = $this->app->make(\Concrete\Core\Application\Service\User::class);
	            $userName = $userService->generateUsernameFromEmail($email);
                 $data['uName'] = $userName;
	            $data['uEmail'] = $email;
	            $password = $this->password_generate(7);
	            $data['uPassword'] = $password;
			  $data['uPasswordConfirm'] = $password;
			  $process = $this->app->make('user/registration')->createFromPublicRegistration($data);
			  $userExists = \UserInfo::getByEmail($email);
			$userIDOrdered = $process->getUserID();
			if (is_object($process)) {
					
					$billingaddress = $order->getAttribute('billing_address');
					$process->setAttribute('billing_first_name',$order->getAttribute("billing_first_name"));
					$process->setAttribute('billing_last_name',$order->getAttribute("billing_last_name"));
					$process->setAttribute('billing_address',$billingaddress);
					$process->setAttribute('billing_phone',$order->getAttribute("billing_phone"));
					$process->setAttribute('billing_company',$order->getAttribute("billing_company"));
					$process->setAttribute('email',$order->getAttribute("email"));
					$process->setAttribute('billing_company',$order->getAttribute("billing_company"));
					
					if (Config::get('community_store.vat_number')) {
						$vat_number = $order->getAttribute('vat_number');
						$process->setAttribute('vat_number',$vat_number);
					}
					
					$billingcompany = $order->getAttribute("billing_company");
					if ($billingcompany) {
						$process->setAttribute('billing_company', $billingcompany);
					}
					
					
					
					 if ($order->isShippable()) {
						 $process->setAttribute('shipping_first_name',$order->getAttribute("shipping_first_name"));
						 $process->setAttribute('shipping_last_name',$order->getAttribute("shipping_last_name"));
						 $process->setAttribute('shipping_company',$order->getAttribute("shipping_company"));
						 $shippingAddress = $order->getAttribute('shipping_address');
						 $process->setAttribute('shipping_address', $shippingAddress);
					}
				
		            $process->markValidated();
		            $process->triggerActivate('register_activate', USER_SUPER_ID);
		            
		            //Email the Password Seperately
		            //community_store_register_password_sent
		            
		            $orderedCustomer = new StoreCustomer($order->getCustomerID());
		            
		            $mh = Core::make('mail');
                    $fromName = Config::get('community_store.emailalertsname');
                    $fromEmail = Config::get('community_store.emailalerts');
                    if (!$fromEmail) {
                        $fromEmail = "store@" . $request->getHost();
                    }
                    if ($fromName) {
                        $mh->from($fromEmail, $fromName);
                    } else {
                        $mh->from($fromEmail);
                    }
                    $mh->to($email);
                    $mh->addParameter('siteName', Config::get('concrete.site'));
                    
                    $mh->addParameter('fullName', $orderedCustomer->getValue('billing_first_name') . ' ' . $orderedCustomer->getValue('billing_last_name'));
                    $mh->addParameter('email', $email);
                    $mh->addParameter('userName', $userName);
                    $mh->addParameter('password', $password);
                    
                    $mh->load('community_store_register_password_sent', 'community_store_register_user');
                    $mh->sendMail();
                    
		            
		        }
	            
	            
            }
            	
            	
            	
            	
            }else{
	            $userIDOrdered = $userExists->getUserID();
            }
            
            
            $order->setCustomerID($userIDOrdered);
            $order->save();
            

        });
    }

    public function uninstall()
    {
        parent::uninstall();
    }

}