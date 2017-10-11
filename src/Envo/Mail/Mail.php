<?php

namespace Envo\Mail;

use Envo\Mail\Event\NewsletterSent;
use Envo\Mail\Event\NewsletterSendFailed;

/**
 * TODO: rewrite this class
 * Should work with any entity
 */

class Mail
{
    protected $view = null;
    public $testing = false;
    public $batchSize = 500;

    /**
     * Get the batch ids
     *
     * @param $batch
     *
     * @return array
     */
    public function getBatchIds($batch)
    {
        return array_map(function($item) {
            $sub = $item->getSubstitutions();
            return $sub['%r.id%'];
        }, $batch);
    }

    /**
     * Handle sendgrid response
     *
     * @param $response
     *
     * @return bool|string
     */
    public function handleSendResponse($response)
    {
        if( $response->statusCode() == 200 || $response->statusCode() == 202 ) {
            return true; // success
        }
        
        return $response->statusCode() . ': '. $response->body();
    }

    /**
     * Generate subscriber batches
     * @param $subscribers
     *
     * @return array
     */
    public function makeBatches($subscribers)
    {
        $personalizations = array();

        foreach ($subscribers as $key => $subscriber) {
            if( ($full = $subscriber->subscriber) ) {
                if( $full->status == $full::STATUS_UNSUBSCRIBED ) {
                    continue;
                }
            }
            
            if( $subscriber->sent_at || $subscriber->delivered_at || $subscriber->failed_at || $subscriber->unsubscribed_at ){
                continue;
            }
            
            $personalization = new \SendGrid\Personalization();
            $to = new \SendGrid\Email(null, $subscriber->subscriber_email);
            $personalization->addTo($to);
            // $personalization->addSubstitution('%recipient.name%', $subscriber->subscriber_name);
            $personalization->addSubstitution('%recipient.email%', $subscriber->subscriber_email);
            $personalization->addSubstitution('%recipient.id%', $subscriber->identifier);
            // SENDGRID doesn't accept numbers. so turn number into string.
            $personalization->addSubstitution('%r.id%', ''.$subscriber->id);
            $personalization->addCustomArg('userid', $subscriber->identifier);
            $personalizations[] = $personalization;
        }
        
        return array_chunk($personalizations, $this->batchSize);
    }

    /**
     * Generate email instance (with content)
     *
     * @param $newsletter
     *
     * @return \SendGrid\Mail
     */
    public function makeMail($newsletter)
    {
        $from = new \SendGrid\Email($newsletter->from_name, $newsletter->from);
        $subject = $newsletter->subject;
        
        $newsletterId = strtotime($newsletter->created_at) . '-' . $newsletter->id;
        
        $content = $this->render(
            APP_PATH . 'app/CommunicationCenter/views/newsletter/default.volt',
            array('newsletter' => $newsletter)
        );
        
        $content = new \SendGrid\Content("text/html", $content);
        $mail = new \SendGrid\Mail();
        $mail->setFrom($from);
        $mail->setSubject($subject);
        $mail->addContent($content);
        $mail->addCustomArg('newsletterid', $newsletterId);
        
        if( $this->testing ) {
            $mail_settings = new \SendGrid\MailSettings();
            $sandbox_mode = new \SendGrid\SandBoxMode();
            $sandbox_mode->setEnable(true);
            $mail_settings->setSandboxMode($sandbox_mode);
            $mail->setMailSettings($mail_settings); // enable testing
        }
        
        return $mail;
    }

    /**
     * Send newsletter
     *
     * @param Newsletter $newsletter
     *
     * @return bool|string
     */
    public function sendNewsletter(Newsletter $newsletter, $subscriberIds = null)
    {
        $validation = $this->validateNewsletter($newsletter);
        if( $validation !== true ) {
            return $validation;
        }

        $mail = $this->makeMail($newsletter);

        if( $subscriberIds ) {
            $subscribers = NewsletterSentRepository::getAllById($subscriberIds);
        }
        else {
            $subscribers = NewsletterSentRepository::getAllByNewsletterId($newsletter->id, $subscriberLimit);
        }

        if( ! $subscribers ) {
            throw new \Exception(\_t('cc.noSubscribersFound'));
        }

        $subscribers = \Lazyload::fromResultset($subscribers, 'subscriber');
        $batches = $this->makeBatches($subscribers);

        if( ! $batches ) {
            return 'Recipients not defined';
        }

        $sendGrid = new \SendGrid(getenv('MAIL_SENDGRID'));
        $sendGridClient = $sendGrid->client;

        $response = true;
        foreach($batches as $batch) {
            $mail->personalization = $batch;

            $response = $sendGridClient->mail()->send()->post($mail);
            $response = $this->handleSendResponse($response);
            
            $subscriberIds = $this->getBatchIds($batch);
            $notice = 'Recipients: ' . count($subscriberIds);

            if( $response !== true ) { // something went wrong
                new NewsletterSendFailed($notice, true, $newsletter, array(
                    'response' => $response, 
                    'subscribers' => $subscriberIds
                ));
                break;
            }
            new NewsletterSent($notice, true, $newsletter, $subscriberIds);
            NewsletterSentRepository::updateSentAtById($subscriberIds, \Date::now());
        }

        if( ! $this->$testing ) {
            // $newsletter->status = $response === true ? Newsletter::STATUS_SENT : Newsletter::STATUS_FAILED;
            // $newsletter->update();
        }

        return $response;
    }

    /**
     * @param Newsletter $newsletter
     *
     * @return bool|array
     */
    public function validateNewsletter(Newsletter $newsletter)
    {
        $data = $newsletter->toArray();
        $validation = \Validator::make($data, array(
            'from' => 'required|email'
        ));

        return $validation->isValid() ? true : $validation->getErrors();
    }

    /**
     * Render path
     */
    public function render($path, $vars = [])
	{
		if( ! \File::exists($path) ) {
			throw new \Exception('Render file path not found');
		}

		if (is_array($vars) && !empty($vars)) {
			extract($vars);
		}

		ob_start();
		include $path;
		return ob_get_clean();
	}

}