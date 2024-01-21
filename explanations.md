## Fonctionnalité

### Constat et solution

J'ai commencé par prendre connaissance du fonctionnement de GH Archive. J'ai d'abord essayé de récupérer des archives datant de 2015 (celles présentées sur les exemples) pour m'apercevoir que les fichiers ont des poids relativement raisonnables pour pouvoir être traités dans un appel synchrone. J'ai ensuite testé sur des années plus récentes et me suis aperçu que les fichiers étaient bien plus lourds (100+ Mo), et que le traitement d'autant de données ne serait pas possible de manière synchrone sans faire exploser la mémoire de PHP.
Pour résoudre ce problème, j'ai opté pour une solution utilisant le composant Messenger de Symfony. Ainsi, les actions de téléchargement et de lecture de l'archive d'une part, et l'action d'ajout d'un Event en base de données d'autre part, sont exécutées de manière asynchrone, chacune dans des processus distincts.

J'ai mis en place des interfaces pour pouvoir fonctionner selon le pattern CQRS (pour la partie commande), puis j'ai implémenté tout d'abord la commande chargée de télécharger l'archive, puis de la lire afin d'extraire les données de chaque Event, un par un (chaque ligne du fichier correspond à un objet Event au format json).
Par la suite, je transmets ces données dans une seconde commande qui va s'occuper de générer les objets issus des entités pour les persister en base de données.

L'exécution asynchrone de ces deux commandes permet d'éviter les limitations de mémoire PHP et d'accélérer le traitement grâce à la parallélisation de l'insertion en base de données.

### Piste d'optmisation

- Je pense qu'il est possible d'avoir des conflits à l'insertion en base, dû au fait que l'on doit persister, en plus de l'objet Event, des objets Actor et Repo. Dans le cas présent, j'ai simplement mis en place une stratégie de retry qui devrait pouvoir éviter au maximum de perdre de la donnée, mais pour avoir une solution plus pérenne, on pourrait envisager de mettre en place un système de lock (avec le composant lock de Symfony par exemple) pour permettre de s'assurer que l'on n'essaie pas de persister deux fois la même entité en parallèle.

- Actuellement pour tester le processus, il faut exécuter manuellement la commande de Messenger pour consommer les messages, avec évidemment un problème de dépassement mémoire sur des gros fichiers puisque tout passe dans un seul processus. Pour résoudre cette problématique, j'ai commencé à mettre en place Supervisor dans l'image docker de PHP. Avec la configuration adéquate, il serait possible d'exécuter plusieurs processus en parallèle et de limiter le nombre de messages consommés par processus et donc éviter les dépassements mémoire.

- Les contrôleurs pourraient être optimisés dans le sens où ils réalisent des actions qui ne les concernent pas. Le SearchController en particulier, qui compile de la données statistique via un repository. Ce traitement pourrait être réalisé dans une Query, avec mise en place d'un système de cache via un middleware configuré dans Messenger, pour éviter de refaire les mêmes requêtes plusieurs fois.

- La structure du projet pourrait être améliorée. N'ayant pas une vision métier suffisament large du sujet, je me suis restraint à la mise en place d'une structure en couche relativement classique.

- Pour la partie persistance des données en base, je suis allé au plus simple en utilisant l'EntityManager de Doctrine, par soucis de temps essentiellement. Il serait plus propre d'utiliser des repository dediés pour Actor et Repo par exemple. On pourrait également réaliser les requêtes manuellement plutôt que d'utiliser Doctrine.

## Refactorisation

- Mise à jour des versions de PHP et Symfony
- Utilisation de Rector pour remplacer les annotation par des attributs
- Utilisation d'un Enum pour les EventType
- Déplacement des fixtures en dehors du dossier src/
- Découpage par couche
- Réécriture des configurations yaml en PHP

-------------------------------------------

## Feature

### Observation and Solution

I began by familiarizing myself with GH Archive. Initially, I attempted to retrieve archives dating back to 2015 (those presented in the examples) to realize that the files had relatively reasonable sizes, making them suitable for synchronous processing. Subsequently, I tested on more recent years and observed that the files were much larger (100+ MB), and processing such data synchronously would not be possible without PHP's memory issues.

I opted for a solution using Symfony's Messenger component. Thus, the actions of downloading and reading the archive on one hand, and the action of adding an Event to the database on the other hand, are executed asynchronously, each in separate processes.

I implemented interfaces to operate according to the CQRS pattern (for the command part). First, I implemented the command responsible for downloading the archive and then reading it to extract data from each Event, one by one (each line of the file corresponds to an Event object in JSON format). Subsequently, I pass this data to a second command that will generate objects from the entities to persist them in the database.

The asynchronous execution of these two commands avoids PHP memory limitations and speeds up processing through parallelization of database insertion.

### Optimization

- I believe conflicts may happen during database insertion due to the need to persist Actor and Repo objects in addition to the Event object. In the current setup, I have simply implemented a retry strategy that should minimize data loss. However, for a more long term solution, we could consider implementing a locking system (using Symfony's Lock component, for example) to ensure that we do not attempt to persist the same entity twice in parallel.

- Currently, to test the process, one must manually execute the Messenger command to consume messages, with an obvious memory overflow issue on large files since everything passes through a single process. To solve this problem, I have started adding Supervisor in the PHP Docker image. With the appropriate configuration, it would be possible to execute multiple processes in parallel and limit the number of messages consumed per process, thus avoiding memory limitation.

- Controllers could be optimized in the sense that they perform actions that do not concern them. The SearchController, in particular, compiles statistical data with a repository. This processing could be performed in a Query (in a CQRS way), with the implementation of a caching system via middleware configured in Messenger, to avoid repeating the same queries multiple times.

- The project structure could be improved. Not having a sufficiently broad business understanding of the subject, I restricted myself to implementing a relatively conventional layered structure.

- For the data persistence part, I opted for simplicity by using Doctrine's EntityManager, mainly due to time constraints. It would be cleaner to use dedicated repositories for Actor and Repo, for example. Also, we could manually build and execute queries rather than using Doctrine.

## Refactoring

- Updating PHP and Symfony versions.
- Using Rector to replace annotations with attributes.
- Implementing an Enum for EventType.
- Relocating fixtures outside the src/ directory.
- Organizing code by layers.
- Rewriting YAML configurations in PHP.
