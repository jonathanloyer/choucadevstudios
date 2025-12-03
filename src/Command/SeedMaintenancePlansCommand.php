<?php

namespace App\Command;

use App\Entity\MaintenancePlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:maintenance-plans',
    description: 'Crée les formules de maintenance par défaut (Care, Guard, Ops)',
)]
class SeedMaintenancePlansCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->em->getRepository(MaintenancePlan::class);

        // Tableau des plans que tu veux toujours avoir en BDD
        $plansData = [
            [
                'name'  => 'Chouca Care',
                'price' => 4900,   // 49,00 € en centimes
            ],
            [
                'name'  => 'Chouca Guard',
                'price' => 7900,   // 79,00 € en centimes
            ],
            [
                'name'  => 'Chouca Ops',
                'price' => 0,      // sur devis
            ],
        ];

        $created = 0;

        foreach ($plansData as $data) {
            $existing = $repo->findOneBy(['name' => $data['name']]);

            if ($existing) {
                $io->text(sprintf('• "%s" existe déjà, on ne fait rien.', $data['name']));
                continue;
            }

            $plan = new MaintenancePlan();
            $plan->setName($data['name']);
            $plan->setPricePerMonth($data['price']);

            $this->em->persist($plan);
            $created++;

            $io->text(sprintf('✓ Plan "%s" créé.', $data['name']));
        }

        if ($created > 0) {
            $this->em->flush();
            $io->success(sprintf('%d plan(s) de maintenance créé(s).', $created));
        } else {
            $io->success('Tous les plans de maintenance étaient déjà en base.');
        }

        return Command::SUCCESS;
    }
}
