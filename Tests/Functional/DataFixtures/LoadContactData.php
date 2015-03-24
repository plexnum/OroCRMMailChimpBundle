<?php

namespace OroCRM\Bundle\MailChimpBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

class LoadContactData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $contactsData = [
        [
            'firstName' => 'Daniel',
            'lastName'  => 'Case',
            'email'     => 'daniel.case@example.com',
        ],
        [
            'firstName' => 'John',
            'lastName'  => 'Case',
            'email'     => 'john.case@example.com',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = $manager->getRepository('OroUserBundle:User')->findOneByUsername('admin');
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        foreach ($this->contactsData as $contactData) {
            $contact = new Contact();
            $contact->setOwner($user);
            $contact->setOrganization($organization);
            $contact->setFirstName($contactData['firstName']);
            $contact->setLastName($contactData['lastName']);
            $email = new ContactEmail();
            $email->setEmail($contactData['email']);
            $email->setPrimary(true);
            $contact->addEmail($email);

            $manager->persist($contact);
        }

        $manager->flush();
    }
}