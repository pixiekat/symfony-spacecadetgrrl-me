<?php
declare(strict_types=1);
namespace App\Controller;

use App\Form;
use App\Services;
use Symfony\Component\Form\FormError;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;

class PagesController extends AbstractController {

  #[Route('/domain-info.html', name: 'app_domain_info')]
  public function domainInfo(HttpFoundation\RequestStack $requestStack): HttpFoundation\Response {
    return $this->render('pages/domain-info.html.twig');
  }


  #[Route('/contact.html', name: 'app_contact')]
  public function contact(HttpFoundation\RequestStack $requestStack, TransportInterface $mailer, Recaptcha3Validator $recaptcha3Validator): HttpFoundation\Response {
    $request = $requestStack->getCurrentRequest();
    $form = $this->createForm(Form\ContactFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $score = $recaptcha3Validator->getLastResponse()->getScore();
      if (!$recaptcha3Validator->getLastResponse()->isSuccess() || $score < 0.5) {
        //$this->addFlash('error', 'There was a problem with your submission. Please check your entries and try again.');
        $form->addError(new FormError('reCAPTCHA validation failed.'));
      }
    }

    if ($form->isSubmitted() && !$form->isValid()) {
      //$this->addFlash('error', 'There was a problem with your submission. Please check your entries and try again.');
      //return $this->render('contact/contact.html.twig', [
      //  'form' => $form->createView(),
      //  'success' => $success ?? false,
      //]);
    }
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      $isJavaScriptEnabled = false; //isset($data['javascript_enabled']) ? $data['javascript_enabled'] : false;

      $from = "{$data['name']} <{$data['email']}>";
      $message = <<<EOT
        Name: {$data['name']}
        Email: {$data['email']}
        Message: {$data['message']}
      EOT;
      $email = (new Email())
        ->from($from)
        ->to("webkitten@spacecadetgrrl.me")
        ->subject("Contact form submission from {$data['name']}")
        ->text(strip_tags($message))
        ->html($message)
      ;
      $mailer->send($email);

      //if ($isJavaScriptEnabled) {
      //  return $this->json(['success' => true, 'message' => 'Contact form submitted successfully!', 'data' => $data]);
      //} else {
        return $this->render('contact/thank_you.html.twig', ['data' => $data]);
      //}
    }
    return $this->render('pages/contact.html.twig', [
      'form' => $form->createView(),
      'success' => $success ?? false,
    ]);
  }

  #[Route('/', name: 'app_homepage')]
  public function index(Services\LastFmManager $lastFmManager): HttpFoundation\Response {
    $lastScrobble = $lastFmManager->getLatestScrobble(true) ?? [];
    return $this->render('homepage/index.html.twig', [
      'lastScrobble' => $lastScrobble,
    ]);
  }
}
