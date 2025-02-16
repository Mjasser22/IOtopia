<?php

namespace App\Tests\Controller;

use App\Entity\Animal;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AnimalControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $animalRepository;
    private string $path = '/animal/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->animalRepository = $this->manager->getRepository(Animal::class);

        foreach ($this->animalRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Animal index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'animal[name]' => 'Testing',
            'animal[species]' => 'Testing',
            'animal[age]' => 'Testing',
            'animal[health_status]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->animalRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Animal();
        $fixture->setName('My Title');
        $fixture->setSpecies('My Title');
        $fixture->setAge('My Title');
        $fixture->setHealth_status('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Animal');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Animal();
        $fixture->setName('Value');
        $fixture->setSpecies('Value');
        $fixture->setAge('Value');
        $fixture->setHealth_status('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'animal[name]' => 'Something New',
            'animal[species]' => 'Something New',
            'animal[age]' => 'Something New',
            'animal[health_status]' => 'Something New',
        ]);

        self::assertResponseRedirects('/animal/');

        $fixture = $this->animalRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getSpecies());
        self::assertSame('Something New', $fixture[0]->getAge());
        self::assertSame('Something New', $fixture[0]->getHealth_status());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Animal();
        $fixture->setName('Value');
        $fixture->setSpecies('Value');
        $fixture->setAge('Value');
        $fixture->setHealth_status('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/animal/');
        self::assertSame(0, $this->animalRepository->count([]));
    }
}
