<?php

namespace App\Tests\Controller;

use App\Entity\SoinDesAnimaux;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SoinDesAnimauxControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $soinDesAnimauxRepository;
    private string $path = '/soin/des/animaux/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->soinDesAnimauxRepository = $this->manager->getRepository(SoinDesAnimaux::class);

        foreach ($this->soinDesAnimauxRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('SoinDesAnimaux index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'soin_des_animaux[Description]' => 'Testing',
            'soin_des_animaux[start_date]' => 'Testing',
            'soin_des_animaux[duration]' => 'Testing',
            'soin_des_animaux[animal]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->soinDesAnimauxRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new SoinDesAnimaux();
        $fixture->setDescription('My Title');
        $fixture->setStart_date('My Title');
        $fixture->setDuration('My Title');
        $fixture->setAnimal('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('SoinDesAnimaux');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new SoinDesAnimaux();
        $fixture->setDescription('Value');
        $fixture->setStart_date('Value');
        $fixture->setDuration('Value');
        $fixture->setAnimal('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'soin_des_animaux[Description]' => 'Something New',
            'soin_des_animaux[start_date]' => 'Something New',
            'soin_des_animaux[duration]' => 'Something New',
            'soin_des_animaux[animal]' => 'Something New',
        ]);

        self::assertResponseRedirects('/soin/des/animaux/');

        $fixture = $this->soinDesAnimauxRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getStart_date());
        self::assertSame('Something New', $fixture[0]->getDuration());
        self::assertSame('Something New', $fixture[0]->getAnimal());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new SoinDesAnimaux();
        $fixture->setDescription('Value');
        $fixture->setStart_date('Value');
        $fixture->setDuration('Value');
        $fixture->setAnimal('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/soin/des/animaux/');
        self::assertSame(0, $this->soinDesAnimauxRepository->count([]));
    }
}
