<?php

// SIARC 2026 — Accreditation operations screens (readers, gates, access rules,
// monitoring, badge lifecycle, sync). Spec-driven: each entry renders through
// pages/siarc/bodies/accred-ops.blade.php in the accreditation design language.
// When an approved design PNG lands for a page, replace it with a bespoke body.

return [

    'siarc.admin.accred.readers' => [
        'title' => 'Reader & Access Devices', 'crumb' => ['Accréditation', 'Lecteurs & Accès'],
        'actions' => [['plus', 'Ajouter un lecteur', 'toast:Appairage d\'un nouveau lecteur à venir…', 'green']],
        'stats' => [
            ['scan-line', '#157A43', '#E2F3E8', '24', 'Lecteurs enregistrés', 'au total'],
            ['wifi', '#3565DE', '#E8EFFB', '21', 'En ligne', '87.5%'],
            ['battery-charging', '#C97A16', '#FDF3E0', '6', 'Lecteurs portatifs', 'sur batterie'],
            ['alert-triangle', '#C0010C', '#FDE8E8', '3', 'Hors ligne', 'à vérifier'],
        ],
        'heads' => ['LECTEUR', 'TYPE', 'EMPLACEMENT', 'STATUT', 'DERNIÈRE ACTIVITÉ', 'LECTURES / JOUR', 'ACTIONS'],
        'rows' => [
            [['t2','RDR-PORTE-A-01','Zebra FX9600'], ['chip','Fixe','#157A43','#E8F5EC'], ['t2','Entrée Principale','Porte A'], ['dot','En ligne','#157A43'], ['t','27/07/2026 09:15:32'], ['t','2 450'], ['act','reader']],
            [['t2','RDR-PORTE-B-01','Zebra FX9600'], ['chip','Fixe','#157A43','#E8F5EC'], ['t2','Entrée Secondaire','Porte B'], ['dot','En ligne','#157A43'], ['t','27/07/2026 09:14:21'], ['t','1 820'], ['act','reader']],
            [['t2','RDR-PORTE-C-01','Zebra FX9600'], ['chip','Fixe','#157A43','#E8F5EC'], ['t2','Pavillon Centre','Porte C'], ['dot','En ligne','#157A43'], ['t','27/07/2026 09:13:11'], ['t','1 640'], ['act','reader']],
            [['t2','RDR-VIP-01','Impinj R700'], ['chip','Fixe','#157A43','#E8F5EC'], ['t2','Salon VIP','Zone VIP'], ['dot','En ligne','#157A43'], ['t','27/07/2026 09:10:44'], ['t','320'], ['act','reader']],
            [['t2','RDR-MOB-04','Zebra TC21'], ['chip','Portatif','#C97A16','#FDF3E0'], ['t2','Équipe volante','Contrôle mobile'], ['dot','En ligne','#157A43'], ['t','27/07/2026 09:02:18'], ['t','740'], ['act','reader']],
            [['t2','RDR-MOB-05','Zebra TC21'], ['chip','Portatif','#C97A16','#FDF3E0'], ['t2','Équipe volante','Contrôle mobile'], ['dot','Hors ligne','#C0010C'], ['t','26/07/2026 18:22:40'], ['t','515'], ['act','reader']],
            [['t2','RDR-PRESSE-01','Impinj R420'], ['chip','Fixe','#157A43','#E8F5EC'], ['t2','Centre Médias','Entrée Presse'], ['dot','Hors ligne','#C0010C'], ['t','26/07/2026 17:05:12'], ['t','210'], ['act','reader']],
        ],
        'railTitle' => 'État du parc', 'railBadge' => 'Temps réel',
        'rail' => [
            ['dl', [['Lecteurs fixes', '18'], ['Lecteurs portatifs', '6'], ['Firmware à jour', '22 / 24'], ['Alertes ouvertes', '3']]],
            ['btn', 'wifi', 'Tester tous les lecteurs', 'toast:Test du parc lancé (démo)'],
            ['btn', 'download', 'Exporter l\'inventaire', 'toast:Export en préparation…'],
        ],
    ],

    'siarc.admin.accred.reader' => [
        'title' => 'RFID Reader Detail', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'RDR-PORTE-A-01'],
        'actions' => [['arrow-left', 'Retour à la liste', 'route:siarc.admin.accred.readers', 'ghost'], ['settings', 'Configurer', 'toast:Configuration du lecteur à venir…', 'green']],
        'stats' => [
            ['scan-line', '#157A43', '#E2F3E8', '2 450', 'Lectures', "aujourd'hui"],
            ['check-circle-2', '#3565DE', '#E8EFFB', '99.2%', 'Taux de succès', 'ce jour'],
            ['timer', '#C97A16', '#FDF3E0', '0.4s', 'Temps de lecture', 'moyen'],
            ['thermometer', '#7C4FE0', '#F0EAFB', '41°C', 'Température', 'nominale'],
        ],
        'heads' => ['DATE / HEURE', 'CARTE', 'TITULAIRE', 'TYPE', 'RÉSULTAT'],
        'tableTitle' => 'Dernières lectures — RDR-PORTE-A-01 · Entrée Principale (Porte A)',
        'rows' => [
            [['t','27/07/2026 09:15:32'], ['t','04 A3 B2 7F 91 6E 80'], ['t2','Jean Paul Essomba','VIP-00024'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','27/07/2026 09:15:18'], ['t','04 A3 B2 7F 91 6E 83'], ['t2','Brenda Tcham','PRE-00078'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','27/07/2026 09:14:55'], ['t','04 A3 B2 7F 91 6E 92'], ['t2','Carte inconnue','—'], ['chip','Entrée','#C0010C','#FDE8E8'], ['ko','Refusé']],
            [['t','27/07/2026 09:14:40'], ['t','04 A3 B2 7F 91 6E 82'], ['t2','Alain Mbarga','VIS-00356'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
        ],
        'railTitle' => 'Informations du lecteur', 'railBadge' => 'En ligne',
        'rail' => [
            ['dl', [['Modèle', 'Zebra FX9600'], ['Firmware', 'v4.2.1 (à jour)'], ['Adresse IP', '10.20.4.11'], ['Antennes', '4 / 4 actives'], ['Zone', 'Entrée Principale'], ['Mise en service', '20/07/2026']]],
            ['btn', 'refresh-cw', 'Redémarrer le lecteur', 'toast:Redémarrage réservé à la production'],
            ['btn', 'scan-line', 'Test de lecture', 'toast:Test de lecture : OK (démo)'],
        ],
    ],

    'siarc.admin.accred.gates' => [
        'title' => 'Gate Management', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Portes'],
        'actions' => [['plus', 'Nouvelle porte', 'toast:Assistant de nouvelle porte à venir…', 'green']],
        'stats' => [
            ['door-open', '#157A43', '#E2F3E8', '12', 'Portes configurées', 'au total'],
            ['unlock', '#3565DE', '#E8EFFB', '9', 'Ouvertes', 'en service'],
            ['lock', '#C97A16', '#FDF3E0', '2', 'Fermées', 'hors horaires'],
            ['alert-triangle', '#C0010C', '#FDE8E8', '1', 'En maintenance', 'Porte E'],
        ],
        'heads' => ['PORTE', 'ZONE', 'MODE', 'LECTEURS', 'STATUT', 'FLUX / HEURE', 'ACTIONS'],
        'rows' => [
            [['t2','Porte A','Entrée Principale'], ['t','Pavillon Officiel'], ['chip','Entrée + Sortie','#157A43','#E8F5EC'], ['t','2'], ['dot','Ouverte','#157A43'], ['t','980'], ['act','gate']],
            [['t2','Porte B','Entrée Secondaire'], ['t','Pavillon Centre'], ['chip','Entrée','#3565DE','#E8EFFB'], ['t','1'], ['dot','Ouverte','#157A43'], ['t','620'], ['act','gate']],
            [['t2','Porte C','Pavillon Centre'], ['t','Zone Exposition'], ['chip','Entrée + Sortie','#157A43','#E8F5EC'], ['t','2'], ['dot','Ouverte','#157A43'], ['t','540'], ['act','gate']],
            [['t2','Porte VIP','Salon VIP'], ['t','Zone VIP'], ['chip','Contrôlée','#C0010C','#FDE8E8'], ['t','1'], ['dot','Ouverte','#157A43'], ['t','85'], ['act','gate']],
            [['t2','Porte D','Salle Conférence A'], ['t','Zones de conférences'], ['chip','Entrée','#3565DE','#E8EFFB'], ['t','1'], ['dot','Fermée','#C97A16'], ['t','—'], ['act','gate']],
            [['t2','Porte E','Sortie de secours Est'], ['t','Pavillon Est'], ['chip','Sortie','#7C4FE0','#F0EAFB'], ['t','1'], ['dot','Maintenance','#C0010C'], ['t','—'], ['act','gate']],
        ],
        'railTitle' => 'Actions de zone', 'railBadge' => 'Sécurisé',
        'rail' => [
            ['dl', [['Ouverture du site', '08:00'], ['Fermeture du site', '20:00'], ['Mode nuit', 'Verrouillage total'], ['Dernier audit', '26/07/2026']]],
            ['btn', 'unlock', 'Ouvrir toutes les portes', 'toast:Commande réservée à la production'],
            ['btn', 'lock', 'Verrouillage d\'urgence', 'toast:Commande réservée à la production'],
        ],
    ],

    'siarc.admin.accred.gate' => [
        'title' => 'Gate Detail', 'crumb' => ['Accréditation', 'Portes', 'Porte A'],
        'actions' => [['arrow-left', 'Retour aux portes', 'route:siarc.admin.accred.gates', 'ghost'], ['settings', 'Configurer la porte', 'toast:Configuration à venir…', 'green']],
        'stats' => [
            ['users-round', '#157A43', '#E2F3E8', '8 240', 'Passages', "aujourd'hui"],
            ['log-in', '#3565DE', '#E8EFFB', '5 130', 'Entrées', '62%'],
            ['log-out', '#C97A16', '#FDF3E0', '3 110', 'Sorties', '38%'],
            ['ban', '#C0010C', '#FDE8E8', '14', 'Refus', "aujourd'hui"],
        ],
        'heads' => ['DATE / HEURE', 'TITULAIRE', 'TYPE DE BADGE', 'SENS', 'RÉSULTAT'],
        'tableTitle' => 'Derniers passages — Porte A · Entrée Principale',
        'rows' => [
            [['t','27/07/2026 09:15:32'], ['t2','Jean Paul Essomba','VIP-00024'], ['chip','VIP','#C0010C','#FDE8E8'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','27/07/2026 09:15:20'], ['t2','Marie Anguissa','EXP-00089'], ['chip','Exposant','#C97A16','#FDF3E0'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','27/07/2026 09:14:55'], ['t2','Carte inconnue','—'], ['chip','—','#8A857A','#F1F0EB'], ['chip','Entrée','#C0010C','#FDE8E8'], ['ko','Refusé']],
            [['t','27/07/2026 09:14:31'], ['t2','David Esono','INT-00034'], ['chip','Intervenant','#7C4FE0','#F0EAFB'], ['chip','Sortie','#3565DE','#E8EFFB'], ['ok','Autorisé']],
        ],
        'railTitle' => 'Configuration de la porte', 'railBadge' => 'Ouverte',
        'rail' => [
            ['dl', [['Mode', 'Entrée + Sortie'], ['Lecteurs', 'RDR-PORTE-A-01 / 02'], ['Zones desservies', 'Pavillon Officiel'], ['Horaires', '08:00 - 20:00'], ['Anti-passback', 'Activé']]],
            ['btn', 'unlock', 'Ouverture manuelle', 'toast:Commande réservée à la production'],
            ['btn', 'history', 'Journal complet', 'toast:Journal complet à venir…'],
        ],
    ],

    'siarc.admin.accred.rules' => [
        'title' => 'Access Rules & Zone Permissions', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Règles d\'accès'],
        'actions' => [['plus', 'Nouvelle règle', 'toast:Éditeur de règle à venir…', 'green']],
        'stats' => [
            ['shield-check', '#157A43', '#E2F3E8', '16', 'Règles actives', 'au total'],
            ['map-pin', '#3565DE', '#E8EFFB', '9', 'Zones contrôlées', 'sur le site'],
            ['users-round', '#C97A16', '#FDF3E0', '8', 'Types de badges', 'couverts'],
            ['alert-triangle', '#C0010C', '#FDE8E8', '2', 'Conflits détectés', 'à arbitrer'],
        ],
        'heads' => ['RÈGLE', 'TYPE DE BADGE', 'ZONES AUTORISÉES', 'PLAGE HORAIRE', 'STATUT', 'ACTIONS'],
        'rows' => [
            [['t2','RG-001 · Accès visiteur standard','Priorité normale'], ['chip','Visiteur','#157A43','#E8F5EC'], ['t2','Pavillons + Expositions','3 zones'], ['t','09:00 - 18:00'], ['dot','Active','#157A43'], ['act','rule']],
            [['t2','RG-002 · Accès exposant','Priorité normale'], ['chip','Exposant','#C97A16','#FDF3E0'], ['t2','Pavillons + Stands','5 zones'], ['t','07:00 - 20:00'], ['dot','Active','#157A43'], ['act','rule']],
            [['t2','RG-003 · Accès complet VIP','Priorité haute'], ['chip','VIP','#C0010C','#FDE8E8'], ['t2','Toutes zones + Salon VIP','9 zones'], ['t','07:00 - 20:00'], ['dot','Active','#157A43'], ['act','rule']],
            [['t2','RG-004 · Intervenants','Priorité normale'], ['chip','Intervenant','#7C4FE0','#F0EAFB'], ['t2','Conférences + Backstage','4 zones'], ['t','08:00 - 19:00'], ['dot','Active','#157A43'], ['act','rule']],
            [['t2','RG-005 · Presse & médias','Priorité normale'], ['chip','Presse','#3565DE','#E8EFFB'], ['t2','Zones presse + Pavillons','4 zones'], ['t','08:00 - 19:00'], ['dot','Active','#157A43'], ['act','rule']],
            [['t2','RG-009 · Zones techniques','Conflit avec RG-003'], ['chip','Staff','#3B382F','#EFEDE6'], ['t2','Zones techniques','2 zones'], ['t','24h/24'], ['dot','Conflit','#C0010C'], ['act','rule']],
        ],
        'railTitle' => 'Matrice des zones', 'railBadge' => '9 zones',
        'rail' => [
            ['chips', 'Zones publiques', [['Pavillons Officiels','#157A43','#E8F5EC'], ['Zones Exposition','#157A43','#E8F5EC'], ['Salles de Conférence','#157A43','#E8F5EC']]],
            ['chips', 'Zones restreintes', [['Zones VIP','#C0010C','#FDE8E8'], ['Backstage','#C0010C','#FDE8E8'], ['Zones Techniques','#C0010C','#FDE8E8']]],
            ['btn', 'git-merge', 'Résoudre les conflits', 'toast:Assistant de résolution à venir…'],
        ],
    ],

    'siarc.admin.accred.rule' => [
        'title' => 'Access Policy Detail', 'crumb' => ['Accréditation', 'Règles d\'accès', 'RG-003'],
        'actions' => [['arrow-left', 'Retour aux règles', 'route:siarc.admin.accred.rules', 'ghost'], ['pencil', 'Modifier la règle', 'toast:Éditeur de règle à venir…', 'green']],
        'stats' => [
            ['users-round', '#157A43', '#E2F3E8', '620', 'Badges concernés', 'type VIP'],
            ['map-pin', '#3565DE', '#E8EFFB', '9', 'Zones autorisées', 'toutes'],
            ['scan-line', '#C97A16', '#FDF3E0', '1 240', 'Passages', "aujourd'hui"],
            ['ban', '#C0010C', '#FDE8E8', '0', 'Refus liés', "aujourd'hui"],
        ],
        'heads' => ['ZONE', 'ACCÈS', 'PLAGE HORAIRE', 'CONDITIONS'],
        'tableTitle' => 'RG-003 · Accès complet VIP — détail des permissions',
        'rows' => [
            [['t2','Pavillon Officiel','PO'], ['chip','Autorisé','#157A43','#E8F5EC'], ['t','07:00 - 20:00'], ['t','—']],
            [['t2','Salon VIP','ZV'], ['chip','Autorisé','#157A43','#E8F5EC'], ['t','07:00 - 20:00'], ['t','Escorte 2 pers. max']],
            [['t2','Backstage','BS'], ['chip','Autorisé','#157A43','#E8F5EC'], ['t','08:00 - 19:00'], ['t','Accompagnement staff']],
            [['t2','Zones Techniques','ZT'], ['chip','Refusé','#C0010C','#FDE8E8'], ['t','—'], ['t','Réservé staff technique']],
        ],
        'railTitle' => 'Métadonnées de la règle', 'railBadge' => 'Active',
        'rail' => [
            ['dl', [['Identifiant', 'RG-003'], ['Type de badge', 'VIP'], ['Priorité', 'Haute'], ['Créée par', 'Jude Nshome'], ['Créée le', '15 Mai 2026'], ['Dernière modification', '24 Mai 2026']]],
            ['btn', 'copy', 'Dupliquer la règle', 'toast:Règle dupliquée (démo)'],
            ['btn', 'power', 'Désactiver la règle', 'toast:Désactivation réservée à la production'],
        ],
    ],

    'siarc.admin.accred.monitor' => [
        'title' => 'Live Access Monitoring', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Monitoring en direct'],
        'actions' => [['refresh-cw', 'Actualiser', 'reload', 'ghost'], ['download', 'Exporter le journal', 'toast:Export du journal en préparation…', 'green']],
        'stats' => [
            ['activity', '#157A43', '#E2F3E8', '142', 'Passages / min', 'en ce moment'],
            ['users-round', '#3565DE', '#E8EFFB', '6 830', 'Présents sur site', 'temps réel'],
            ['check-circle-2', '#C97A16', '#FDF3E0', '99.1%', 'Taux d\'autorisation', "aujourd'hui"],
            ['ban', '#C0010C', '#FDE8E8', '14', 'Refus', "aujourd'hui"],
        ],
        'heads' => ['HEURE', 'POINT D\'ACCÈS', 'TITULAIRE', 'TYPE DE BADGE', 'SENS', 'RÉSULTAT'],
        'tableTitle' => 'Flux en direct — tous les points d\'accès',
        'rows' => [
            [['t','09:15:32'], ['t2','Porte A','Entrée Principale'], ['t2','Jean Paul Essomba','VIP-00024'], ['chip','VIP','#C0010C','#FDE8E8'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','09:15:28'], ['t2','Porte C','Pavillon Centre'], ['t2','Marie Anguissa','EXP-00089'], ['chip','Exposant','#C97A16','#FDF3E0'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','09:15:11'], ['t2','Porte VIP','Salon VIP'], ['t2','Brenda Tcham','PRE-00078'], ['chip','Presse','#3565DE','#E8EFFB'], ['chip','Entrée','#C0010C','#FDE8E8'], ['ko','Refusé']],
            [['t','09:14:55'], ['t2','Porte A','Entrée Principale'], ['t2','Carte inconnue','—'], ['chip','—','#8A857A','#F1F0EB'], ['chip','Entrée','#C0010C','#FDE8E8'], ['ko','Refusé']],
            [['t','09:14:40'], ['t2','Porte B','Entrée Secondaire'], ['t2','Alain Mbarga','VIS-00356'], ['chip','Visiteur','#157A43','#E8F5EC'], ['chip','Entrée','#157A43','#E8F5EC'], ['ok','Autorisé']],
            [['t','09:14:22'], ['t2','Porte D','Salle Conférence A'], ['t2','David Esono','INT-00034'], ['chip','Intervenant','#7C4FE0','#F0EAFB'], ['chip','Sortie','#3565DE','#E8EFFB'], ['ok','Autorisé']],
        ],
        'railTitle' => 'Alertes en direct', 'railBadge' => '2 actives',
        'rail' => [
            ['alert', 'Refus répété — Porte VIP', 'Badge PRE-00078 refusé 3× en 5 min (zone non autorisée).', '#C0010C', '#FDE8E8'],
            ['alert', 'Carte inconnue — Porte A', 'UID non enregistré présenté à 09:14:55.', '#C97A16', '#FDF3E0'],
            ['btn', 'siren', 'Notifier la sécurité', 'toast:Notification envoyée à la sécurité (démo)'],
        ],
    ],

    'siarc.admin.accred.failures' => [
        'title' => 'Failed Access Log', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Échecs d\'accès'],
        'actions' => [['download', 'Exporter', 'toast:Export du journal en préparation…', 'green']],
        'stats' => [
            ['ban', '#C0010C', '#FDE8E8', '14', 'Refus', "aujourd'hui"],
            ['map-pin', '#C97A16', '#FDF3E0', '5', 'Zone non autorisée', 'motif principal'],
            ['credit-card', '#3565DE', '#E8EFFB', '4', 'Cartes inconnues', "aujourd'hui"],
            ['clock', '#7C4FE0', '#F0EAFB', '3', 'Hors plage horaire', "aujourd'hui"],
        ],
        'heads' => ['DATE / HEURE', 'POINT D\'ACCÈS', 'CARTE / TITULAIRE', 'MOTIF DU REFUS', 'SUITE DONNÉE', 'ACTIONS'],
        'rows' => [
            [['t','27/07/2026 09:15:11'], ['t2','Porte VIP','Salon VIP'], ['t2','Brenda Tcham','PRE-00078'], ['chip','Zone non autorisée','#C0010C','#FDE8E8'], ['t','Redirigée zone presse'], ['act','failure']],
            [['t','27/07/2026 09:14:55'], ['t2','Porte A','Entrée Principale'], ['t2','Carte inconnue','04 A3 B2 7F 91 6E 92'], ['chip','UID non enregistré','#C97A16','#FDF3E0'], ['t','Sécurité notifiée'], ['act','failure']],
            [['t','27/07/2026 08:52:03'], ['t2','Porte D','Salle Conférence A'], ['t2','Samuel Ndongo','STF-00122'], ['chip','Hors plage horaire','#7C4FE0','#F0EAFB'], ['t','Accès à 09:00'], ['act','failure']],
            [['t','27/07/2026 08:31:47'], ['t2','Porte B','Entrée Secondaire'], ['t2','Priska Nguimatsia','VOL-00056'], ['chip','Badge désactivé','#C0010C','#FDE8E8'], ['t','Renvoyée à l\'accréditation'], ['act','failure']],
            [['t','26/07/2026 17:45:10'], ['t2','Porte A','Entrée Principale'], ['t2','Badge perdu déclaré','VIS-00212'], ['chip','Badge bloqué','#C0010C','#FDE8E8'], ['t','Confiscation'], ['act','failure']],
        ],
        'railTitle' => 'Répartition des motifs', 'railBadge' => "Aujourd'hui",
        'rail' => [
            ['bars', [['Zone non autorisée', 5, '#C0010C'], ['UID non enregistré', 4, '#C97A16'], ['Hors plage horaire', 3, '#7C4FE0'], ['Badge désactivé / bloqué', 2, '#3565DE']]],
            ['btn', 'download', 'Rapport des refus (PDF)', 'toast:Rapport en préparation…'],
        ],
    ],

    'siarc.admin.accred.override' => [
        'title' => 'Manual Access Override', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Override manuel'],
        'actions' => [['shield-alert', 'Nouvel override', 'toast:Un override requiert une double validation (démo)', 'green']],
        'stats' => [
            ['key-round', '#157A43', '#E2F3E8', '6', 'Overrides', "aujourd'hui"],
            ['user-check', '#3565DE', '#E8EFFB', '4', 'Approuvés', 'double validation'],
            ['clock', '#C97A16', '#FDF3E0', '1', 'En attente', 'de validation'],
            ['x-circle', '#C0010C', '#FDE8E8', '1', 'Rejeté', "aujourd'hui"],
        ],
        'heads' => ['RÉFÉRENCE', 'BÉNÉFICIAIRE', 'ZONE / PORTE', 'MOTIF', 'VALIDÉ PAR', 'STATUT', 'ACTIONS'],
        'rows' => [
            [['t','OVR-2026-0012'], ['t2','Délégation MINPMEESA','4 personnes'], ['t2','Salon VIP','Porte VIP'], ['t','Visite officielle imprévue'], ['t2','Jude Nshome','+ Chef sécurité'], ['dot','Approuvé','#157A43'], ['act','override']],
            [['t','OVR-2026-0011'], ['t2','Équipe CRTV','2 personnes'], ['t2','Backstage','Porte D'], ['t','Captation cérémonie'], ['t2','Jude Nshome','+ Resp. programme'], ['dot','Approuvé','#157A43'], ['act','override']],
            [['t','OVR-2026-0010'], ['t2','Prestataire électricité','1 personne'], ['t2','Zones Techniques','Porte E'], ['t','Intervention urgente'], ['t2','En attente','2e validation requise'], ['dot','En attente','#C97A16'], ['act','override']],
            [['t','OVR-2026-0009'], ['t2','Visiteur sans badge','1 personne'], ['t2','Pavillon Officiel','Porte A'], ['t','Badge oublié'], ['t2','Rejeté','Réédition du badge exigée'], ['dot','Rejeté','#C0010C'], ['act','override']],
        ],
        'railTitle' => 'Procédure d\'override', 'railBadge' => 'Double validation',
        'rail' => [
            ['steps', [['1', 'Demande motivée', 'Agent de porte ou superviseur'], ['2', 'Première validation', 'Responsable accréditation'], ['3', 'Seconde validation', 'Chef de la sécurité'], ['4', 'Accès tracé', 'Journalisé + limité dans le temps']]],
            ['btn', 'history', 'Journal des overrides', 'toast:Journal complet à venir…'],
        ],
    ],

    'siarc.admin.accred.lost' => [
        'title' => 'Lost / Blocked Badge Management', 'crumb' => ['Accréditation', 'Badges', 'Perdus & bloqués'],
        'actions' => [['plus', 'Déclarer une perte', 'toast:Déclaration de perte enregistrée (démo)', 'green']],
        'stats' => [
            ['badge-alert', '#C0010C', '#FDE8E8', '9', 'Badges déclarés perdus', 'depuis l\'ouverture'],
            ['lock', '#C97A16', '#FDF3E0', '7', 'Bloqués', 'immédiatement'],
            ['refresh-ccw', '#3565DE', '#E8EFFB', '6', 'Remplacés', 'réédités'],
            ['search', '#157A43', '#E2F3E8', '2', 'Retrouvés', 'réactivés'],
        ],
        'heads' => ['BADGE', 'TITULAIRE', 'DÉCLARÉ LE', 'MOTIF', 'STATUT', 'REMPLACEMENT', 'ACTIONS'],
        'rows' => [
            [['t','VIS-00212'], ['t2','Colette Abena','Visiteuse'], ['t','26/07/2026 17:30'], ['chip','Perte déclarée','#C0010C','#FDE8E8'], ['dot','Bloqué','#C0010C'], ['t2','VIS-00398','réédité 26/07'], ['act','lost']],
            [['t','EXP-00147'], ['t2','Atelier Ndam','Exposant'], ['t','26/07/2026 14:12'], ['chip','Vol présumé','#C0010C','#FDE8E8'], ['dot','Bloqué','#C0010C'], ['t2','EXP-00251','réédité 26/07'], ['act','lost']],
            [['t','VIS-00178'], ['t2','Paul Etoga','Visiteur'], ['t','26/07/2026 11:05'], ['chip','Perte déclarée','#C0010C','#FDE8E8'], ['dot','Retrouvé','#157A43'], ['t2','—','réactivé 26/07'], ['act','lost']],
            [['t','PRE-00033'], ['t2','Radio Tiemeni','Presse'], ['t','25/07/2026 16:44'], ['chip','Badge endommagé','#C97A16','#FDF3E0'], ['dot','Remplacé','#3565DE'], ['t2','PRE-00102','réédité 25/07'], ['act','lost']],
        ],
        'railTitle' => 'Procédure de blocage', 'railBadge' => 'Immédiat',
        'rail' => [
            ['steps', [['1', 'Déclaration', 'Guichet accréditation ou hotline'], ['2', 'Blocage instantané', 'QR + RFID révoqués sur tous les lecteurs'], ['3', 'Vérification d\'identité', 'Pièce officielle exigée'], ['4', 'Réédition', 'Nouveau badge, nouvel UID']]],
            ['btn', 'ban', 'Bloquer un badge maintenant', 'toast:Blocage réservé à la production'],
        ],
    ],

    'siarc.admin.accred.activation' => [
        'title' => 'Badge Activation / Deactivation', 'crumb' => ['Accréditation', 'Badges', 'Activation'],
        'actions' => [['download', 'Exporter', 'toast:Export en préparation…', 'ghost'], ['power', 'Activation en lot', 'toast:Activation en lot réservée à la production', 'green']],
        'stats' => [
            ['badge-check', '#157A43', '#E2F3E8', '4 620', 'Badges actifs', 'sur 4 850 émis'],
            ['badge-minus', '#C97A16', '#FDF3E0', '196', 'Désactivés', 'volontairement'],
            ['badge-alert', '#C0010C', '#FDE8E8', '25', 'Bloqués', 'perte / sécurité'],
            ['badge-plus', '#3565DE', '#E8EFFB', '9', 'En attente', "d'activation"],
        ],
        'heads' => ['BADGE', 'TITULAIRE', 'TYPE', 'ÉTAT ACTUEL', 'DERNIER CHANGEMENT', 'PAR', 'ACTIONS'],
        'rows' => [
            [['t','VIP-00024'], ['t2','Jean Paul Essomba','Ministère des Arts et de la Culture'], ['chip','VIP','#C0010C','#FDE8E8'], ['dot','Actif','#157A43'], ['t','27/07/2026 07:00'], ['t','Système'], ['act','activation']],
            [['t','EXP-00126'], ['t2','Alain Mbarga','Entreprise MB Solutions'], ['chip','Exposant','#C97A16','#FDF3E0'], ['dot','Actif','#157A43'], ['t','27/07/2026 07:00'], ['t','Système'], ['act','activation']],
            [['t','VOL-00056'], ['t2','Priska Nguimatsia','Bénévole'], ['chip','Bénévole','#0E8F83','#DFF3F1'], ['dot','Désactivé','#C97A16'], ['t','26/07/2026 18:00'], ['t','Jude Nshome'], ['act','activation']],
            [['t','VIS-00212'], ['t2','Colette Abena','Visiteuse'], ['chip','Visiteur','#157A43','#E8F5EC'], ['dot','Bloqué','#C0010C'], ['t','26/07/2026 17:31'], ['t','Jude Nshome'], ['act','activation']],
            [['t','INT-00051'], ['t2','Nouvel intervenant','Confirmation en attente'], ['chip','Intervenant','#7C4FE0','#F0EAFB'], ['dot','En attente','#3565DE'], ['t','26/07/2026 15:20'], ['t','Système'], ['act','activation']],
        ],
        'railTitle' => 'Règles d\'état', 'railBadge' => 'Automatisé',
        'rail' => [
            ['dl', [['Activation', 'Auto à l\'impression'], ['Désactivation', 'Manuelle ou fin d\'événement'], ['Blocage', 'Immédiat, tous lecteurs'], ['Réactivation', 'Après vérification d\'identité']]],
            ['btn', 'power', 'Activer / désactiver un badge', 'toast:Action réservée à la production'],
        ],
    ],

    'siarc.admin.accred.replace' => [
        'title' => 'Badge Replacement Flow', 'crumb' => ['Accréditation', 'Badges', 'Remplacement'],
        'actions' => [['plus', 'Nouveau remplacement', 'toast:Assistant de remplacement démarré (démo)', 'green']],
        'stats' => [
            ['refresh-ccw', '#3565DE', '#E8EFFB', '11', 'Remplacements', 'depuis l\'ouverture'],
            ['timer', '#157A43', '#E2F3E8', '4 min', 'Délai moyen', 'blocage → réédition'],
            ['credit-card', '#C97A16', '#FDF3E0', '8', 'Avec nouvel UID RFID', 'réencodés'],
            ['banknote', '#7C4FE0', '#F0EAFB', '5 000 F', 'Frais de réédition', 'par badge perdu'],
        ],
        'heads' => ['DOSSIER', 'ANCIEN BADGE', 'NOUVEAU BADGE', 'MOTIF', 'ÉTAPE', 'ACTIONS'],
        'rows' => [
            [['t2','RMP-2026-0011','Colette Abena'], ['t2','VIS-00212','bloqué'], ['t2','VIS-00398','actif'], ['chip','Perte','#C0010C','#FDE8E8'], ['dot','Terminé','#157A43'], ['act','replace']],
            [['t2','RMP-2026-0010','Atelier Ndam'], ['t2','EXP-00147','bloqué'], ['t2','EXP-00251','actif'], ['chip','Vol présumé','#C0010C','#FDE8E8'], ['dot','Terminé','#157A43'], ['act','replace']],
            [['t2','RMP-2026-0009','Radio Tiemeni'], ['t2','PRE-00033','désactivé'], ['t2','PRE-00102','actif'], ['chip','Endommagé','#C97A16','#FDF3E0'], ['dot','Terminé','#157A43'], ['act','replace']],
            [['t2','RMP-2026-0012','Nouvel exposant','—'], ['t2','EXP-00250','bloqué'], ['t2','En impression','file PQ-2026-00046'], ['chip','Erreur d\'impression','#3565DE','#E8EFFB'], ['dot','En cours','#C97A16'], ['act','replace']],
        ],
        'railTitle' => 'Étapes du remplacement', 'railBadge' => '4 étapes',
        'rail' => [
            ['steps', [['1', 'Blocage de l\'ancien badge', 'QR + UID révoqués'], ['2', 'Vérification d\'identité', 'Pièce officielle + photo'], ['3', 'Réédition', 'Nouveau code, nouvel UID, print queue'], ['4', 'Remise & signature', 'Émargement du titulaire']]],
            ['btn', 'printer', 'Voir la file d\'impression', 'route:siarc.admin.accred.queue'],
        ],
    ],

    'siarc.admin.accred.revocations' => [
        'title' => 'Badge Revocation History', 'crumb' => ['Accréditation', 'Badges', 'Révocations'],
        'actions' => [['download', 'Exporter l\'historique', 'toast:Export en préparation…', 'green']],
        'stats' => [
            ['ban', '#C0010C', '#FDE8E8', '25', 'Révocations', 'au total'],
            ['badge-alert', '#C97A16', '#FDF3E0', '9', 'Pour perte', '36%'],
            ['shield-alert', '#7C4FE0', '#F0EAFB', '3', 'Pour sécurité', 'incidents'],
            ['undo-2', '#157A43', '#E2F3E8', '2', 'Annulées', 'badges retrouvés'],
        ],
        'heads' => ['DATE / HEURE', 'BADGE', 'TITULAIRE', 'MOTIF', 'DÉCIDÉ PAR', 'RÉVERSIBLE'],
        'rows' => [
            [['t','26/07/2026 17:31'], ['t','VIS-00212'], ['t2','Colette Abena','Visiteuse'], ['chip','Perte déclarée','#C0010C','#FDE8E8'], ['t','Jude Nshome'], ['chip','Oui — 48h','#157A43','#E8F5EC']],
            [['t','26/07/2026 14:13'], ['t','EXP-00147'], ['t2','Atelier Ndam','Exposant'], ['chip','Vol présumé','#C0010C','#FDE8E8'], ['t','Chef sécurité'], ['chip','Non','#8A857A','#F1F0EB']],
            [['t','26/07/2026 10:40'], ['t','VIS-00190'], ['t2','Comportement signalé','Incident #114'], ['chip','Sécurité','#7C4FE0','#F0EAFB'], ['t','Chef sécurité'], ['chip','Non','#8A857A','#F1F0EB']],
            [['t','25/07/2026 16:45'], ['t','PRE-00033'], ['t2','Radio Tiemeni','Presse'], ['chip','Endommagé','#C97A16','#FDF3E0'], ['t','Jude Nshome'], ['chip','Oui — remplacé','#157A43','#E8F5EC']],
            [['t','25/07/2026 09:12'], ['t','VIS-00178'], ['t2','Paul Etoga','Visiteur'], ['chip','Perte déclarée','#C0010C','#FDE8E8'], ['t','Jude Nshome'], ['chip','Annulée — retrouvé','#3565DE','#E8EFFB']],
        ],
        'railTitle' => 'Politique de révocation', 'railBadge' => 'Traçée',
        'rail' => [
            ['dl', [['Effet', 'Immédiat sur tous les lecteurs'], ['Journalisation', 'Horodatée + auteur'], ['Réversibilité', '48h pour les pertes'], ['Notification', 'Titulaire + sécurité']]],
            ['btn', 'history', 'Journal d\'audit complet', 'toast:Journal complet à venir…'],
        ],
    ],

    'siarc.admin.accred.health' => [
        'title' => 'RFID Reader Health Monitoring', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Santé des lecteurs'],
        'actions' => [['refresh-cw', 'Actualiser', 'reload', 'ghost'], ['bell', 'Configurer les alertes', 'toast:Paramètres d\'alertes à venir…', 'green']],
        'stats' => [
            ['heart-pulse', '#157A43', '#E2F3E8', '21 / 24', 'Lecteurs sains', 'en ligne'],
            ['thermometer', '#C97A16', '#FDF3E0', '43°C', 'Température max', 'RDR-PORTE-C-01'],
            ['battery-low', '#C0010C', '#FDE8E8', '1', 'Batterie faible', 'RDR-MOB-05'],
            ['cpu', '#3565DE', '#E8EFFB', 'v4.2.1', 'Firmware cible', '22 / 24 à jour'],
        ],
        'heads' => ['LECTEUR', 'UPTIME', 'SIGNAL', 'TEMPÉRATURE', 'BATTERIE', 'FIRMWARE', 'ÉTAT'],
        'rows' => [
            [['t2','RDR-PORTE-A-01','Entrée Principale'], ['t','99.98%'], ['prog',96,'#157A43'], ['t','39°C'], ['t','Secteur'], ['chip','v4.2.1','#157A43','#E8F5EC'], ['dot','Sain','#157A43']],
            [['t2','RDR-PORTE-B-01','Entrée Secondaire'], ['t','99.95%'], ['prog',91,'#157A43'], ['t','40°C'], ['t','Secteur'], ['chip','v4.2.1','#157A43','#E8F5EC'], ['dot','Sain','#157A43']],
            [['t2','RDR-PORTE-C-01','Pavillon Centre'], ['t','99.90%'], ['prog',88,'#157A43'], ['t','43°C'], ['t','Secteur'], ['chip','v4.2.1','#157A43','#E8F5EC'], ['dot','Surveillance','#C97A16']],
            [['t2','RDR-MOB-04','Équipe volante'], ['t','98.10%'], ['prog',74,'#C97A16'], ['t','36°C'], ['prog',62,'#157A43'], ['chip','v4.2.1','#157A43','#E8F5EC'], ['dot','Sain','#157A43']],
            [['t2','RDR-MOB-05','Équipe volante'], ['t','91.40%'], ['prog',0,'#C0010C'], ['t','—'], ['prog',8,'#C0010C'], ['chip','v4.1.8','#C97A16','#FDF3E0'], ['dot','Hors ligne','#C0010C']],
            [['t2','RDR-PRESSE-01','Centre Médias'], ['t','95.20%'], ['prog',0,'#C0010C'], ['t','—'], ['t','Secteur'], ['chip','v4.1.8','#C97A16','#FDF3E0'], ['dot','Hors ligne','#C0010C']],
        ],
        'railTitle' => 'Maintenance', 'railBadge' => '2 tickets',
        'rail' => [
            ['alert', 'RDR-MOB-05 — batterie 8%', 'Mettre en charge ou permuter avec un lecteur de réserve.', '#C0010C', '#FDE8E8'],
            ['alert', 'RDR-PRESSE-01 — hors ligne', 'Vérifier l\'alimentation PoE du Centre Médias.', '#C97A16', '#FDF3E0'],
            ['btn', 'cpu', 'Déployer firmware v4.2.1', 'toast:Déploiement réservé à la production'],
        ],
    ],

    'siarc.admin.accred.sync' => [
        'title' => 'Offline Synchronization Dashboard', 'crumb' => ['Accréditation', 'Lecteurs & Accès', 'Synchronisation'],
        'actions' => [['refresh-cw', 'Synchroniser maintenant', 'toast:Synchronisation forcée lancée (démo)', 'green']],
        'stats' => [
            ['cloud-check', '#157A43', '#E2F3E8', '22 / 24', 'Appareils synchronisés', 'temps réel'],
            ['cloud-off', '#C0010C', '#FDE8E8', '2', 'En mode hors ligne', 'cache local actif'],
            ['database', '#3565DE', '#E8EFFB', '1 240', 'Lectures en attente', 'de remontée'],
            ['clock', '#C97A16', '#FDF3E0', '09:14:50', 'Dernière synchro', 'complète'],
        ],
        'heads' => ['APPAREIL', 'MODE', 'CACHE LOCAL', 'EN ATTENTE', 'DERNIÈRE SYNCHRO', 'ÉTAT'],
        'rows' => [
            [['t2','RDR-PORTE-A-01','Entrée Principale'], ['chip','Temps réel','#157A43','#E8F5EC'], ['t','4 850 badges'], ['t','0'], ['t','09:14:50'], ['dot','Synchronisé','#157A43']],
            [['t2','RDR-PORTE-B-01','Entrée Secondaire'], ['chip','Temps réel','#157A43','#E8F5EC'], ['t','4 850 badges'], ['t','0'], ['t','09:14:50'], ['dot','Synchronisé','#157A43']],
            [['t2','RDR-MOB-04','Équipe volante'], ['chip','Différé (4G)','#C97A16','#FDF3E0'], ['t','4 850 badges'], ['t','120'], ['t','09:12:05'], ['dot','Synchronisé','#157A43']],
            [['t2','RDR-MOB-05','Équipe volante'], ['chip','Hors ligne','#C0010C','#FDE8E8'], ['t','4 812 badges'], ['t','515'], ['t','26/07 18:22'], ['dot','En attente','#C97A16']],
            [['t2','RDR-PRESSE-01','Centre Médias'], ['chip','Hors ligne','#C0010C','#FDE8E8'], ['t','4 812 badges'], ['t','605'], ['t','26/07 17:05'], ['dot','En attente','#C97A16']],
        ],
        'railTitle' => 'Mode hors ligne', 'railBadge' => 'Résilient',
        'rail' => [
            ['dl', [['Cache embarqué', 'Liste complète des badges'], ['Décisions locales', 'Autorisation sans réseau'], ['File de remontée', 'Lectures re-synchronisées'], ['Révocations', 'Poussées en priorité']]],
            ['bars', [['Fraîcheur du cache — fixes', 100, '#157A43'], ['Fraîcheur du cache — mobiles', 92, '#C97A16']]],
            ['btn', 'database', 'Pousser la liste de révocation', 'toast:Push de révocation réservé à la production'],
        ],
    ],
];
