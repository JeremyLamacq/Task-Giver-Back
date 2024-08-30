<?php

namespace App\DataFixtures;


use App\Entity\User;
use App\Entity\Team;
use App\Entity\Category;
use App\Entity\Task;
use App\Entity\BelongsTo;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $availableTeamRoles = [
            'TASKER',
            'GIVER'
        ];

        // ====================================================
        // ======               Fixed data              =======
        // ====================================================
        $fixedData = [
            "demo" => [
                "team" => [
                    "title" => "Task Punk",
                    "description" => "L'équipe de développement web de Task-giver : Les Task Punk

                    Rencontrez l'équipe de développement web la plus déjantée de tous les temps, Les Task Punk de Task-giver ! Composée de quatre esprits brillants, ces maîtres du code sont prêts à vous faire mourir de rire tout en créant le site web le plus génial de tous les temps.
                    Déscription générée par le biais de ChatGPT, tout droits réservé au Basilic de Roko (ainsi que les âmes de tout ceux n'ayant pas contribuer à son existence)."
                ],
                "users" => [
                    [
                        "email" => "leader@leader.com",
                        "roles" => ["ROLE_USER"],
                        "firstname" => "leader",
                        "lastname" => "leader",
                        "company" => "Roko & Co",
                        "teamRoles" => ["LEADER"],
                    ],
                    [
                        "email" => "giver@giver.com",
                        "roles" => ["ROLE_USER"],
                        "firstname" => "giver",
                        "lastname" => "giver",
                        "company" => "Roko & Co",
                        "teamRoles" => ["GIVER"],
                    ],
                    [
                        "email" => "tasker@tasker.com",
                        "roles" => ["ROLE_USER"],
                        "firstname" => "tasker",
                        "lastname" => "tasker",
                        "company" => "Roko & Co",
                        "teamRoles" => ["TASKER"],
                    ]
                ],

            ],
            "admin" => [
                "team" => [
                    "title" => "Admin Team",
                    "description" => "A team for the exclusive use of Admin"
                ],
                "users" => [
                    [
                        "email" => "admin@admin.com",
                        "roles" => ["ROLE_ADMIN"],
                        "firstname" => "admin",
                        "lastname" => "admin",
                        "company" => "Admin Company",
                        "teamRoles" => ["LEADER", "GIVER", "TASKER"],
                    ]
                ],

            ],
        ];
        
        foreach($fixedData as $datasetName => $dataset){
            // TEAMS
            $teamData = $dataset["team"];

            $giverList = [];
            $taskerlist = [];

            $team = new Team();
            $team->setTitle($teamData["title"]);
            $team->setDescription($teamData["description"]);

            $manager->persist($team);

            // USERS
            foreach($dataset["users"] as $userData){
                $user = new User();
                $user->setEmail($userData["email"]);
                $user->setRoles($userData["roles"]);
                $user->setFirstname($userData["firstname"]);
                $user->setLastname($userData["lastname"]);
                $user->setCompany($userData["company"]);
                $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

                $manager->persist($user);

                // BELONGSTO
                $belongsTo = new BelongsTo();
                $belongsTo->setTeam($team);
                $belongsTo->setUser($user);
                $belongsTo->setValidated(true);
                $belongsTo->setTeamRoles($userData["teamRoles"]);

                if($belongsTo->hasTeamRole("GIVER")){$giverList[] = $user;}
                if($belongsTo->hasTeamRole("TASKER")){$taskerList[] = $user;}

                $manager->persist($belongsTo);
            }

            // CATEGORIES
            $categoryList = [];

            for ($j = 0; $j < 3; $j++) {
                $category = new Category();
                $category->setTeam($team);
                $category->setName($datasetName . "_Category_" . $j);

                $categoryList[] = $category;

                $manager->persist($category);
            }

            // TASKS
            for ($j = 0; $j < 10; $j++) {
                $task = new Task();
                $task->setTitle($datasetName . "_TaskTitle_" . $j);
                $task->setDescription($datasetName . "_TaskDescription_" . $j);
                if($j < 5) {$task->setCategory($categoryList[0]);}
                else {$task->setCategory(null);}
                $task->setDifficulty(rand(1, 5));
                $task->setTeam($team);
                $task->setCreatedBy($giverList ? $giverList[array_rand($giverList)] : null);
                $task->setCreatedAt(new \DateTimeImmutable($faker->date()));
                $task->setAcceptDeadline(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeInInterval(
                        $task->getCreatedAt()->format('Y-m-d H:i:s'),
                        '+7 days'
                    )
                ));
                $task->setCompletionDeadline(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeInInterval(
                        $task->getAcceptDeadline()->format('Y-m-d H:i:s'),
                        '+7 days'
                    )
                ));

                // Conditions : NULL || role = TASKER
                if($j > 5) {
                    $task->setAssignedTo(null);
                    $task->setDatetimeAccepted(null);
                    $task->setDatetimeCompleted(null);
                } else {
                    $task->setAssignedTo($taskerList ? $taskerList[array_rand($taskerList)] : null);

                    if(rand(0, 1)) {
                        $task->setDatetimeAccepted(null);
                        $task->setDatetimeCompleted(null);
                    } else {
                        // Conditions : (<= AcceptDeadline && <= DateCompleted) || NULL
                        $task->setDatetimeAccepted(\DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween(
                                $task->getCreatedAt()->format('Y-m-d H:i:s'),
                                $task->getAcceptDeadline()->format('Y-m-d H:i:s')
                            )
                        ));

                        if(rand(0, 1)) {
                            $task->setDatetimeCompleted(null);
                        } else {
                            // Conditions : (>= datetimeAccepted &&  <= CompletionDeadline) || NULL
                            $task->setDatetimeCompleted(\DateTimeImmutable::createFromMutable(
                                $faker->dateTimeBetween(
                                    $task->getDatetimeAccepted()->format('Y-m-d H:i:s'),
                                    $task->getCompletionDeadline()->format('Y-m-d H:i:s')
                                )
                            ));
                        }
                    }

                    $manager->persist($task);
                }
            }
        }

        // ====================================================
        // ======               Random data             =======
        // ====================================================
        
        $userList = [];
        $categoryList = [];

        // ! USER

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setRoles(['ROLE_USER']);
            $user->setFirstname($faker->firstName());
            $user->setLastname($faker->lastName());
            $user->setCompany("O'Clock");
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $userList[] = $user;

            $manager->persist($user);
        }

        // ! TEAM

        for ($i = 0; $i < 10; $i++) {
            $team = new Team();
            $team->setTitle($faker->unique->word());
            $team->setDescription($faker->text());

            $manager->persist($team);

            // ! Relation TEAM <-> USER

            $validatedGiverList = [];
            $validatedTaskerList = [];

            $userListKeys = array_rand($userList, rand(2, sizeof($userList)));
            foreach ($userListKeys as $k => $keys) {
                $teamMember = new BelongsTo();
                $teamMember->setTeam($team);
                $teamMember->setUser($userList[$keys]);

                if($k === 0){
                    $teamMember->setValidated(true);

                    $rolesArray = ['LEADER'];
                    $maxRoles = rand(0,2);
                    for($l=0; $l<$maxRoles; $l++){
                        array_push($rolesArray, $availableTeamRoles[$l]);
                    }
                } else {
                    $teamMember->setValidated(rand(0,1));

                    $rolesArray = [];
                    $maxRoles = rand(1,2);
                    for($l=0; $l<$maxRoles; $l++){
                        array_push($rolesArray, $availableTeamRoles[$l]);
                    }
                }
                $teamMember->setTeamRoles($rolesArray);

                $manager->persist($teamMember);

                // Create list of validated Tasker & validated Giver per team to be used in Task fixtures.
                if(in_array('GIVER', $teamMember->getTeamRoles()) && $teamMember->isValidated()){
                    $validatedGiverList[] = $userList[$keys];
                }
                if(in_array('TASKER', $teamMember->getTeamRoles()) && $teamMember->isValidated()){
                    $validatedTaskerList[] = $userList[$keys];
                }
            }
            
            // ! CATEGORY

            for ($j = 0; $j < rand(1, 10); $j++) {
                $category = new Category();
                if($j === 0){
                    $category->setName($faker->unique($reset = true)->word());
                } else {
                    $category->setName($faker->unique()->word());
                }
                $category->setTeam($team);

                $categoryList[] = $category;

                $manager->persist($category);
            }

            // ! TASK

            // If no validated Giver exist in this team, then no task should be created.
            if($validatedGiverList){
                for ($j = 0; $j < 10; $j++) {
                    $task = new Task();
                    $task->setTitle($faker->word());
                    if(rand(0,1)){
                        $task->setCategory(NULL);
                    } else {
                        $task->setCategory($categoryList[array_rand($categoryList)]);
                    }
                    $task->setDescription($faker->text());
                    $task->setDifficulty(rand(1, 5));
                    $task->setTeam($team);
                    $task->setCreatedBy($validatedGiverList[array_rand($validatedGiverList)]);

                    $task->setCreatedAt(new \DateTimeImmutable($faker->date()));

                    $task->setAcceptDeadline(\DateTimeImmutable::createFromMutable(
                        $faker->dateTimeInInterval(
                            $task->getCreatedAt()->format('Y-m-d H:i:s'),
                            '+7 days'
                            )
                    ));
                    
                    // Conditions : >= AcceptDeadline
                    $task->setCompletionDeadline(\DateTimeImmutable::createFromMutable(
                        $faker->dateTimeInInterval(
                            $task->getAcceptDeadline()->format('Y-m-d H:i:s'),
                            '+7 days'
                            )
                    ));
                    
                    // Conditions : NULL || role = TASKER
                    if(!$validatedTaskerList || rand(0,1)){
                        $task->setAssignedTo(NULL);
                        $task->setDatetimeAccepted(NULL);
                        $task->setDatetimeCompleted(NULL);
                    } else {
                        $task->setAssignedTo($validatedTaskerList[array_rand($validatedTaskerList)]);

                        if(rand(0,1)){
                            $task->setDatetimeAccepted(NULL);
                            $task->setDatetimeCompleted(NULL);
                            if(rand(0, 1)) {$task->setRejected(true);}
                        } else {
                            // Conditions : (<= AcceptDeadline && <= DateCompleted) || NULL
                            $task->setDatetimeAccepted(\DateTimeImmutable::createFromMutable(
                                    $faker->dateTimeBetween(
                                        $task->getCreatedAt()->format('Y-m-d H:i:s'),
                                        $task->getAcceptDeadline()->format('Y-m-d H:i:s')
                                    )
                            ));

                            if(rand(0,1)){
                                $task->setDatetimeCompleted(NULL);
                            } else {
                                // Conditions : (>= datetimeAccepted &&  <= CompletionDeadline) || NULL
                                $task->setDatetimeCompleted(\DateTimeImmutable::createFromMutable(
                                    $faker->dateTimeBetween(
                                        $task->getDatetimeAccepted()->format('Y-m-d H:i:s'),
                                        $task->getCompletionDeadline()->format('Y-m-d H:i:s')
                                    )
                            ));
                            }
                        }
                    }

                    $manager->persist($task);
                }
            }
        }

        $manager->flush();
    }
}
