// delivraptor_server.c - Serveur principal Délivraptor
// Compilation : cc main2.c $(mysql_config --cflags --libs) -o delivraptor_server


#include <mysql/mysql.h>      // Bibliothèque MySQL pour accès à la base de données
#include <stdio.h>            // Entrées/sorties standard (printf, fprintf, fopen, etc.)
#include <stdlib.h>           // Fonctions standard (malloc, exit, atoi, etc.)
#include <string.h>           // Manipulation de chaînes (strlen, strcmp, strcpy, etc.)
#include <time.h>             // Gestion du temps (rand seed, timestamps, localtime)
#include <unistd.h>           // Fonctions système Unix (close, read, write, fork)
#include <arpa/inet.h>        // Conversion d'adresses IP (inet_ntoa, inet_ntop, htons, etc.)
#include <sys/socket.h>       // Programmation des sockets (socket, bind, listen, accept, etc.)
#include <ctype.h>            // Vérification/conversion de caractères (isxdigit, etc.)
#include <getopt.h>           // Parsing des options en ligne de commande (getopt_long)
#include <libgen.h>           // Manipulation de chemins (basename pour extraire nom du fichier)
#include <sys/wait.h>         // Gestion des processus fils (waitpid pour éviter les zombies)
#include <signal.h>           // Gestion des signaux (SIGCHLD pour détecter fin des processus fils)
#include <errno.h>            // Gestion des codes d'erreur (errno)

// Variable extern pour errno (standard C)
extern int errno;
MYSQL *conn = NULL;
char *global_log_file = NULL;
struct ServerConfig *global_config = NULL;

// Constantes définissant les limites de taille des buffers
#define BUFFER_SIZE 1024
#define MAX_LINE_LENGTH 256
#define MAX_LOG_MESSAGE 512
#define PORT_SERVER_DEFAULT 8080

// Structure pour stocker l'état d'une session client
// Chaque client connecté au serveur a sa propre session associée
// Cette structure est transmise au processus fils qui gère le client
struct ClientSession {
    int client_socket;
    int authentified;
    char username[50];
    char client_ip[INET_ADDRSTRLEN];
    int client_port;
    pid_t pid;
};

// Structure pour la configuration du serveur
struct ServerConfig {
    int port;
    int capacity;
    char *auth_file;
    char *log_file;
};

// FONCTIONS DE LOGGING

/**
 * get_timestamp() - Retourne un timestamp formaté pour les logs
 * 
 * Génère une chaîne de caractères représentant l'heure actuelle
 * au format : YYYY-MM-DD HH:MM:SS
 * 
 * IMPORTANT : Utilise une variable statique
 * donc le contenu est écrasé à chaque appel
 * 
 * Retour: char* - pointeur vers la chaîne de timestamp (variable statique)
 * 
 * Exemple de sortie : "2026-01-09 14:32:05"
 */
char* get_timestamp() {
    static char timestamp[20];
    time_t now = time(NULL);
    struct tm *t = localtime(&now);
    
    // Formate le timestamp : année-mois-jour heure:minute:seconde
    snprintf(timestamp, sizeof(timestamp), "%04d-%02d-%02d %02d:%02d:%02d",
             t->tm_year + 1900, 
             t->tm_mon + 1,
             t->tm_mday,
             t->tm_hour,
             t->tm_min,
             t->tm_sec);
    
    return timestamp;
}

/**
 * write_log() - Écrit un message dans le fichier de log et sur la console
 * 
 * Fonction centralisée de logging qui écrit dans le fichier de logs
 * ET affiche sur la console pour le monitoring en temps réel.
 * 
 * Format du log : [TIMESTAMP] [IP:PORT] [USERNAME] ACTION: message
 * 
 * Les paramètres optionnels (ip, port, username) permettent de construire
 * un préfixe contextuel. Si absents, ils sont pas dnas les logs.
 * 
 * Paramètres:
 *   - log_file: chemin vers le fichier de log (ouvert en mode append)
 *   - ip: adresse IP du client (peut être NULL)
 *   - port: port du client (ignoré si <= 0)
 *   - username: nom d'utilisateur (peut être NULL)
 *   - action: type d'action (AUTH, CREATE, STATUS, etc.)
 *   - message: message détaillé de l'événement
 * 
 * Exemple de sortie:
 *   [2026-01-09 14:32:05] [192.168.1.100:54321] [alice] AUTH: Authentification réussie
 */
void write_log(const char *log_file, const char *ip, int port, 
               const char *username, const char *action, const char *message) {
    // Ouvre le fichier de log en mode "append" (ajout en fin de fichier)
    FILE *log = fopen(log_file, "a");
    if (!log) {
        perror("Erreur d'ouverture du fichier de log");
        return;
    }
    
    // Construction du préfixe contextuel avec IP:PORT et username
    char prefix[100] = "";
    
    if (ip && port > 0) {
        // Cas 1 : IP + PORT + USERNAME
        if (username && strlen(username) > 0) {
            snprintf(prefix, sizeof(prefix), "[%s:%d] [%s]", ip, port, username);
        } 
        // Cas 2 : IP + PORT seulement (client non authentifié)
        else {
            snprintf(prefix, sizeof(prefix), "[%s:%d]", ip, port);
        }
    } 
    // Cas 3 : Pas de préfixe
    
    // Écriture dans le fichier de log
    // Format : [TIMESTAMP] [PREFIXE] ACTION: message
    fprintf(log, "[%s] %s %s: %s\n", 
            get_timestamp(),
            prefix,
            action,
            message);
    
    fclose(log);
    
    // Affiche aussi dans la console en direct
    printf("[%s] %s %s: %s\n", 
           get_timestamp(), 
           prefix,
           action,
           message);
}

/**
 * log_server_start() - Enregistre le démarrage du serveur dans les logs
 * 
 * Fonction appelée au démarrage du serveur pour tracer l'événement
 * et enregistrer la configuration utilisée.
 * 
 * Paramètres:
 *   - config: structure contenant la configuration du serveur
 *   - server_ip: adresse IP du serveur
 */
void log_server_start(struct ServerConfig config, const char *server_ip) {
    char message[256];
    
    // Construit un message récapitulant tous les paramètres de démarrage
    snprintf(message, sizeof(message), 
             "Serveur démarré - Port: %d, Capacité: %d, Auth: %s, Log: %s",
             config.port,
             config.capacity,
             config.auth_file,
             config.log_file);
    
    // Écrit dans les logs avec l'action "START"
    write_log(config.log_file, server_ip, config.port, 
              "SERVER", "START", message);
}

/**
 * log_server_stop() - Enregistre l'arrêt du serveur dans les logs
 * 
 * Fonction appelée lors de l'arrêt propre du serveur (Ctrl+C, signal, etc.)
 * pour tracer l'événement de fermeture.
 * 
 * Paramètres:
 *   - config: structure contenant la configuration du serveur
 *   - server_ip: adresse IP du serveur (généralement "0.0.0.0")
 */
void log_server_stop(struct ServerConfig config, const char *server_ip) {
    write_log(config.log_file, server_ip, config.port, 
              "SERVER", "STOP", "Serveur arrêté proprement");
}

// FONCTIONS BASE DE DONNÉES

/**
 * config_BD() - Initialise une nouvelle connexion à la base de données MySQL
 * 
 * Cette fonction crée et retourne une NOUVELLE connexion à la base de données.
 * Elle est appelée :
 * - Une fois par le processus père au démarrage
 * - Une fois par chaque processus fils pour gérer un client
 * 
 * IMPORTANT : Chaque processus fils DOIT avoir sa propre connexion MySQL
 * pour éviter les conflits d'accès concurrent.
 * 
 * Étapes:
 * 1. Initialise une structure de connexion MySQL vide
 * 2. Établit une connexion au serveur MySQL local
 * 3. Sélectionne automatiquement la base de données "delivraptor"
 * 
 * Paramètres MySQL :
 * - host: "localhost" (serveur MySQL sur la machine locale)
 * - user: "pperche" (utilisateur MySQL avec droits sur delivraptor)
 * - password: "grognasseEtCompagnie" (mot de passe MySQL)
 * - database: "delivraptor" (nom de la base de données)
 * - port: 0 (utilise le port par défaut 3306)
 * - socket: NULL (utilise le socket par défaut)
 * - flags: 0 (pas d'options spéciales)
 * 
 * Retour: MYSQL* - pointeur vers la connexion établie
 * Sortie: Quitte le programme (exit) en cas d'erreur
 */
MYSQL* config_BD() {
    MYSQL *local_conn = mysql_init(NULL);
    if (!local_conn) {
        fprintf(stderr, "mysql_init failed\n");
        exit(EXIT_FAILURE);
    }

    
    if (!mysql_real_connect(local_conn, "localhost", "pperche", "grognasseEtCompagnie", "delivraptor", 0, NULL, 0)) {
        fprintf(stderr, "Connexion échouée : %s\n", mysql_error(local_conn));
        mysql_close(local_conn);
        exit(EXIT_FAILURE);
    }
    
    printf("Connexion à la base de données MySQL établie\n");
    return local_conn;
}

/**
 * num_bordereau_unique() - Génère un numéro de bordereau aléatoire à 10 chiffres
 * 
 * Génère un nombre aléatoire de exactement 10 chiffres pour identifier
 * de manière unique chaque colis/bordereau dans le système.
 * 
 * Algorithme :
 * - Commence avec 0
 * - 10 itérations : décale vers la gauche (×10) et ajoute un chiffre aléatoire (0-9)
 * - Résultat : nombre entre 0000000000 et 9999999999
 * 
 * IMPORTANT : Nécessite srand() pour initialiser le générateur aléatoire
 * (fait dans main() avec srand(time(NULL)))
 * 
 * Retour: long long - numéro de bordereau unique à 10 chiffres
 * 
 * Exemple de sortie : 3847562019, 0012345678, 9999999999
 */
long long num_bordereau_unique() {
    long long num = 0;
    
    for (int i = 0; i < 10; i++) {
        num = num * 10 + (rand() % 10);
    }
    return num;
}

/**
 * get_capacite_actuelle() - Récupère le nombre de colis actuellement dans la file
 * 
 * Cette fonction compte les colis en attente de prise en charge dans la table
 * _delivraptor_file_prise_en_charge. Cette table est utilisée pour gérer
 * la capacité maximale du système.
 * 
 * Logique métier :
 * - Quand un colis est créé (étape 1), il est ajouté à cette file
 * - Quand un colis passe à l'étape 5, il est retiré de cette file
 * - Le nombre d'entrées dans cette table = charge actuelle du système
 * 
 * Paramètres:
 *   - conn: connexion MySQL active à utiliser pour la requête
 * 
 * Retour: 
 *   - int >= 0 : nombre de colis dans la file
 *   - int = -1 : erreur lors de la requête
 */
int get_capacite_actuelle(MYSQL *conn) {
    char query[256];
    
    snprintf(query, sizeof(query), 
             "SELECT COUNT(*) FROM _delivraptor_file_prise_en_charge");
    
    if (mysql_query(conn, query)) {
        return -1;
    }
    
    // Récupère le résultat de la requête
    MYSQL_RES *result = mysql_store_result(conn);
    if (!result) {
        return -1;
    }
    
    // Extrait la première (et unique) ligne du résultat
    MYSQL_ROW row = mysql_fetch_row(result);
    int current_load = row ? atoi(row[0]) : 0;
    
    mysql_free_result(result);
    
    return current_load;
}

/**
 * liberer_file_colis() - Retire un colis de la file de prise en charge
 * 
 * Appelée quand un colis passe de l'étape 4 (en livraison) à l'étape 5 (livré).
 * Libère une place dans la file, permettant la prise en charge d'un nouveau colis.
 * 
 * usage typique :
 * 1. Client crée un bordereau (CREATE) -> insertion dans _delivraptor_file_prise_en_charge
 * 2. Simulateur fait avancer le colis (étapes 1->2->3->4)
 * 3. Colis livré (étape 4->5) -> appel de cette fonction
 * 4. Place libérée -> nouveau bordereau peut être créé
 * 
 * Paramètres:
 *   - conn: connexion MySQL active
 *   - bordereau: numéro du bordereau à retirer de la file
 */
void liberer_file_colis(MYSQL *conn, long long bordereau) {
    char query[256];
    
    snprintf(query, sizeof(query),
             "DELETE FROM _delivraptor_file_prise_en_charge WHERE numBordereau = %lld",
             bordereau);
    
    if (mysql_query(conn, query)) {
        fprintf(stderr, "Erreur libération file: %s\n", mysql_error(conn));
    } else {
        printf("Colis %lld libéré de la file (passage étape 4->5)\n", bordereau);
    }
}

// FONCTIONS AUTHENTIFICATION

/**
 * is_valid_md5() - Vérifie si une chaîne a un format MD5 valide
 */
int is_valid_md5(const char *str) {
    if (strlen(str) != 32) {
        return 0;
    }
    
    for (int i = 0; i < 32; i++) {
        if (!isxdigit(str[i]))
            return 0;
    }
    return 1;
}

/**
 * check_auth() - Vérifie les identifiants d'un utilisateur contre un fichier
 */
int check_auth(const char *auth_file, const char *username, const char *password_md5) {
    FILE *file = fopen(auth_file, "r");
    if(!file) {
        printf("Impossible d'ouvrir le fichier d'authentification\n");
        return 0;
    }

    char line[MAX_LINE_LENGTH];
    int authentified = 0;

    while (fgets(line, sizeof(line), file)) {
        line[strcspn(line, "\n")] = 0;

        if (line[0] == '#' || strlen(line) == 0) {
            continue;
        }

        char *colon = strchr(line, ':');
        if (!colon) {
            continue;
        }

        *colon = '\0';
        char *file_user = line;
        char *file_password_md5 = colon + 1;

        if(strcmp(file_user, username) == 0 && strcmp(file_password_md5, password_md5) == 0) {
            authentified = 1;
            break;
        }
    }

    fclose(file);
    return authentified;
}

/**
 * auth() - Traite la commande d'authentification AUTH
 */
void auth(struct ClientSession *session, char *username, char *password_md5, 
          char *auth_file, struct ServerConfig config, MYSQL *conn) {
    char reponse[BUFFER_SIZE];

    if(session->authentified) {
        snprintf(reponse, sizeof(reponse), "ERROR ALREADY_AUTHENTICATED\n");
        send(session->client_socket, reponse, strlen(reponse), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "AUTH", "Tentative d'authentification alors que déjà authentifié");
        return;
    }

    if(!username || !password_md5) {
        snprintf(reponse, sizeof(reponse), "ERROR MISSING_CREDENTIALS\n");
        send(session->client_socket, reponse, strlen(reponse), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  NULL, "AUTH", "Identifiants manquants dans la requête");
        return;
    }

    if (!is_valid_md5(password_md5)) {
        snprintf(reponse, sizeof(reponse), "ERROR INVALID_MD5_FORMAT\n");
        send(session->client_socket, reponse, strlen(reponse), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  NULL, "AUTH", "Format MD5 invalide");
        return;
    }

    if(check_auth(auth_file, username, password_md5)) {
        session->authentified = 1;
        strncpy(session->username, username, sizeof(session->username)-1);
        session->username[sizeof(session->username)-1] = '\0';
        snprintf(reponse, sizeof(reponse), "AUTH_SUCCESS\n");
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "AUTH", "Authentification réussie");
    } else {
        snprintf(reponse, sizeof(reponse), "ERROR AUTH_FAILED\n");
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  NULL, "AUTH", "Échec d'authentification - identifiants invalides");
    }

    send(session->client_socket, reponse, strlen(reponse), 0);
}

/**
 * require_auth() - Vérifie que le client est authentifié
 */
int require_auth(struct ClientSession *session) {
    if(!session->authentified) {
        char response[] = "ERROR NOT_AUTHENTICATED\n";
        send(session->client_socket, response, strlen(response), 0);
        return 0;
    }
    return 1;
}

// FONCTIONS COMMANDES PROTOCOLE

/**
 * create() - Traite la commande de création de bordereau CREATE
 */
void create(struct ClientSession *session, char *commande_id, 
            struct ServerConfig config, MYSQL *conn) {
    if(!require_auth(session)) {
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", "Tentative sans authentification");
        return;
    }

    char query[512];
    char response[BUFFER_SIZE];
    
    // Vérifier si un bordereau existe déjà pour cette commande
    snprintf(query, sizeof(query), 
             "SELECT numBordereau FROM _delivraptor_colis WHERE noCommande = %s", commande_id);
    
    if (mysql_query(conn, query)) {
        snprintf(response, sizeof(response), "ERROR DB_QUERY\n");
        send(session->client_socket, response, strlen(response), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", "Erreur DB_QUERY lors de la vérification d'existence");
        return;
    }
    
    MYSQL_RES *result = mysql_store_result(conn);
    if (result && mysql_num_rows(result) > 0) {
        MYSQL_ROW row = mysql_fetch_row(result);
        snprintf(response, sizeof(response), "BORDEREAU %s\n", row[0]);
        send(session->client_socket, response, strlen(response), 0);
        mysql_free_result(result);
        
        char log_msg[256];
        snprintf(log_msg, sizeof(log_msg), "Bordereau existant: %s", row[0]);
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", log_msg);
        return;
    }
    mysql_free_result(result);
    
    // Vérifier la capacité actuelle du système
    int current_load = get_capacite_actuelle(conn);
    if (current_load < 0) {
        snprintf(response, sizeof(response), "ERROR DB_QUERY_CAPACITY\n");
        send(session->client_socket, response, strlen(response), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", "Erreur DB_QUERY_CAPACITY");
        return;
    }
    
    // Vérifie si on peut ajouter un nouveau colis
    if (current_load >= config.capacity) {
        snprintf(response, sizeof(response), "ERROR CAPACITE\n");
        send(session->client_socket, response, strlen(response), 0);
        
        char log_msg[256];
        snprintf(log_msg, sizeof(log_msg), "Capacité maximale atteinte (actuel: %d, max: %d)", current_load, config.capacity);
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", log_msg);
        return;
    }
    
    // Générer un nouveau numéro de bordereau unique
    long long new_bordereau = num_bordereau_unique();
    
    // Insérer le nouveau colis dans la base de données
    snprintf(query, sizeof(query),
             "INSERT INTO _delivraptor_colis(numBordereau, noCommande, destination, localisation, etape, date_etape) "
             "VALUES (%lld, %s, 'En attente chez Alizon', 'Entrepôt Alizon', 1, NOW())",
             new_bordereau, commande_id);
    
    if (mysql_query(conn, query)) {
        snprintf(response, sizeof(response), "ERROR DB_INSERT\n");
        send(session->client_socket, response, strlen(response), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", "Erreur DB_INSERT lors de l'insertion");
        return;
    }
    
    // Insertion dans la file de prise en charge (CRITIQUE pour la gestion de capacité)
    snprintf(query, sizeof(query),
             "INSERT INTO _delivraptor_file_prise_en_charge (numBordereau, date_entree) VALUES (%lld, NOW())",
             new_bordereau);
    
    if (mysql_query(conn, query)) {
        snprintf(response, sizeof(response), "ERROR DB_INSERT_FILE\n");
        send(session->client_socket, response, strlen(response), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "CREATE", "Erreur insertion dans la file");
        
        // Rollback: supprimer le colis créé
        snprintf(query, sizeof(query), "DELETE FROM _delivraptor_colis WHERE numBordereau = %lld", new_bordereau);
        mysql_query(conn, query);
        return;
    }
    
    // Renvoyer le nouveau numéro de bordereau au client
    snprintf(response, sizeof(response), "BORDEREAU %lld\n", new_bordereau);
    send(session->client_socket, response, strlen(response), 0);
    
    write_log(config.log_file, session->client_ip, session->client_port,
              session->username, "CREATE", "Nouveau bordereau créé");
}

/**
 * status() - Traite la commande de consultation de statut STATUS
 */
void status(struct ClientSession *session, char *bordereau, struct ServerConfig config, MYSQL *conn) {
    if(!require_auth(session)) {
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "STATUS", "Tentative sans authentification");
        return;
    }

    char query[256];
    char response[BUFFER_SIZE];

    snprintf(query, sizeof(query),
             "SELECT etape, localisation, date_etape, livraison_type, refus_raison, photo_path "
             "FROM _delivraptor_colis WHERE numBordereau = %s", bordereau);
    
    if (mysql_query(conn, query)) {
        snprintf(response, sizeof(response), "ERROR DB_QUERY\n");
        send(session->client_socket, response, strlen(response), 0);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "STATUS", bordereau);
        return;
    }
    
    MYSQL_RES *result = mysql_store_result(conn);
    if (!result || mysql_num_rows(result) == 0) {
        snprintf(response, sizeof(response), "ERROR NOT_FOUND\n");
        send(session->client_socket, response, strlen(response), 0);
        if (result) mysql_free_result(result);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "STATUS", bordereau);
        return;
    }
    
    MYSQL_ROW row = mysql_fetch_row(result);
    
    snprintf(response, sizeof(response), 
             "ETAPE %s|%s|%s|%s|%s\n",
             row[0] ? row[0] : "",
             row[1] ? row[1] : "",
             row[2] ? row[2] : "",
             row[3] ? row[3] : "",
             row[4] ? row[4] : "");
    
    send(session->client_socket, response, strlen(response), 0);
    
    write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "STATUS", bordereau);
    // Vérifier si besoin de libérer la file (étape 4 -> 5)
    if (row[0] && atoi(row[0]) == 5) {
        long long num_bordereau = atoll(bordereau);
        liberer_file_colis(conn, num_bordereau);
        
        write_log(config.log_file, session->client_ip, session->client_port,
                  session->username, "STATUS", bordereau);
    }
    
    // Gestion de l'image pour l'étape 9
    if (row[0] && atoi(row[0]) == 9 && row[5] && strlen(row[5]) > 0) {
        FILE *img_file = fopen(row[5], "rb");
        if (img_file) {
            send(session->client_socket, "---IMAGE---\n", 12, 0);
            
            char buffer[1024];
            size_t bytes_read;
            
            while ((bytes_read = fread(buffer, 1, sizeof(buffer), img_file)) > 0) {
                send(session->client_socket, buffer, bytes_read, 0);
            }
            fclose(img_file);
            
            write_log(config.log_file, session->client_ip, session->client_port,
                      session->username, "STATUS", bordereau);
        }
    }
    
    mysql_free_result(result);
}

/**
 * help() - Traite la commande HELP du protocole
 */
void help(struct ClientSession *session) {
    char *help_text = 
        "╔══════════════════════════════════════════════════════════════╗\n"
        "║               PROTOCOLE DÉLIVRAPTOR - COMMANDES              ║\n"
        "╚══════════════════════════════════════════════════════════════╝\n\n"
        "AUTHENTIFICATION (requise pour les autres commandes):\n"
        "  AUTH <username> <md5_password>\n"
        "  Exemple: AUTH alizon e10adc3949ba59abbe56e057f20f883e\n\n"
        "GESTION DES COLIS:\n"
        "  CREATE <commande_id>      Crée un nouveau bordereau de livraison\n"
        "  STATUS <bordereau>        Consulte l'état d'un colis\n\n"
        "INFORMATIONS:\n"
        "  HELP                      Affiche cette aide\n"
        "  QUIT                      Déconnexion propre\n"
        "  EXIT                      Alias de QUIT\n\n"
        "FORMAT DES RÉPONSES:\n"
        "  • CREATE   → BORDEREAU <num>\n"
        "  • STATUS   → ETAPE <num>|<localisation>|<date>|<type>|<raison>\n"
        "  • AUTH     → AUTH_SUCCESS ou ERROR AUTH_FAILED\n"
        "  • ERREURS  → ERROR <type> (CAPACITE, NOT_FOUND, DB_QUERY, etc.)\n\n"
        "NOTES:\n"
        "  • Toutes les commandes sont en MAJUSCULES\n"
        "  • Le hash MD5 doit être 32 caractères hexadécimaux\n"
        "  • L'authentification est valide pour toute la session\n"
        "  • Une commande = une ligne terminée par \\n\n"
        "╔══════════════════════════════════════════════════════════════╗\n"
        "║                 Service Délivraptor - v1.0                   ║\n"
        "╚══════════════════════════════════════════════════════════════╝\n";
    
    send(session->client_socket, help_text, strlen(help_text), 0);
}

/**
 * print_help() - Affiche l'aide en ligne du serveur
 */
void print_help(const char *prog_name) {
    const char *base_name = basename((char *)prog_name);
    printf("╔══════════════════════════════════════════════════════════════╗\n");
    printf("║              SERVEUR DÉLIVRAPTOR - R3.05 SAÉ 3               ║\n");
    printf("╚══════════════════════════════════════════════════════════════╝\n\n");
    
    printf("DESCRIPTION:\n");
    printf("  Simulateur de transport et de livraison de colis pour Alizon.\n");
    printf("  Gère la prise en charge, le suivi et la livraison des colis.\n\n");
    
    printf("UTILISATION:\n");
    printf("  %s [OPTIONS]\n\n", prog_name);
    
    printf("OPTIONS:\n");
    printf("  -p, --port PORT        Port TCP d'écoute (1-65535)\n");
    printf("                          Défaut: 8080\n");
    printf("                          Exemple: -p 9090 ou --port 9090\n\n");
    
    printf("  -c, --capacity CAP     Capacité maximale de la file de livraison\n");
    printf("                          Nombre de colis simultanés en étape 1-4\n");
    printf("                          Défaut: 5\n");
    printf("                          Exemple: -c 10 ou --capacity 10\n\n");
    
    printf("  -a, --auth FILE        Fichier d'authentification des clients\n");
    printf("                          Format: username:md5hash (un par ligne)\n");
    printf("                          Défaut: /etc/delivraptor/auth.txt\n");
    printf("                          Exemple: -a /home/user/auth.txt\n\n");
    
    printf("  -l, --log FILE         Fichier de logs du serveur\n");
    printf("                          Défaut: /var/log/delivraptor.log\n");
    printf("                          Exemple: -l /tmp/debug.log\n\n");
    
    printf("  -h, --help             Affiche cette aide et quitte\n\n");
    
    printf("EXEMPLES D'UTILISATION:\n");
    printf("  %s -p 8080 -c 5 -a auth.txt\n", base_name);
    printf("    Lance le serveur sur le port 8080 avec capacité 5\n\n");
    
    printf("  %s --port 9090 --capacity 10 --auth /etc/delivraptor/auth.txt\n", base_name);
    printf("    Lance le serveur sur le port 9090 avec capacité 10\n\n");
    
    printf("  %s --help\n", base_name);
    printf("    Affiche ce message d'aide\n\n");
    
    printf("NOTES:\n");
    printf("  • Le serveur nécessite une connexion MySQL/MariaDB fonctionnelle\n");
    printf("  • Le fichier d'authentification doit être au format username:md5hash\n");
    printf("  • Pour générer un hash MD5 : echo -n 'motdepasse' | md5sum\n");
    printf("  • Les logs incluent date, IP client et actions effectuées\n");
    printf("  • Utilisez Ctrl+C pour arrêter le serveur\n");
    
    printf("\n╔══════════════════════════════════════════════════════════════╗\n");
    printf("║              IUT Lannion - Département Informatique          ║\n");
    printf("╚══════════════════════════════════════════════════════════════╝\n");
}

/**
 * gerer_sigchld() - Gestionnaire de signal pour les processus fils terminés
 */
void gerer_sigchld(int sig) {
    (void)sig; 
    
    int saved_errno = errno;
    
    while (waitpid(-1, NULL, WNOHANG) > 0);
    
    errno = saved_errno;
}

/**
 * gerer_client() - Fonction exécutée par chaque processus fils pour gérer un client
 * 
 * Cette fonction est le point d'entrée du processus fils après fork().
 * Chaque client a son propre processus dédié qui :
 * - Crée sa propre connexion à la base de données
 * - Gère une session complète (authentification + commandes)
 * - Se termine quand le client se déconnecte
 * 
 * Architecture multi-processus :
 * - Processus père : écoute les nouvelles connexions (accept)
 * - Processus fils : gère la communication avec UN client
 * - Isolation : crash d'un fils n'affecte pas les autres clients ni le serveur
 * 
 * Cycle de vie :
 * 1. Création connexion BDD propre au fils
 * 2. Boucle de lecture/traitement des commandes
 * 3. Fermeture connexion BDD et socket
 * 4. exit() pour terminer le processus fils
 * 
 * Paramètres:
 *   - session: structure contenant les infos du client (socket, IP, port)
 *   - config: configuration du serveur (port, capacité, fichiers auth/log)
 */
void gerer_client(struct ClientSession session, struct ServerConfig config) {
    // ÉTAPE 1 : CRÉATION CONNEXION BDD DU FILS
    
    // Chaque processus fils DOIT avoir sa propre connexion MySQL
    MYSQL *conn_client = config_BD();
    if (!conn_client) {
        // Échec de connexion BDD : impossible de gérer le client
        close(session.client_socket);
        exit(EXIT_FAILURE);
    }
    
    // Log de démarrage du processus fils
    write_log(config.log_file, session.client_ip, session.client_port,
              NULL, "CONNECT", "Nouveau processus fils pour gestion client");
    
    // ÉTAPE 2 : BOUCLE DE TRAITEMENT DES COMMANDES
    
    int connection_active = 1;  // Flag : 1 = connexion ouverte, 0 = client a demandé QUIT
    
    while (connection_active) {
        char buffer[BUFFER_SIZE];
        
        // read() : lit les données envoyées par le client sur le socket
        // Bloque jusqu'à réception de données ou fermeture de connexion
        ssize_t bytes_read = read(session.client_socket, buffer, sizeof(buffer)-1);
        
        // Vérification du résultat de la lecture
        if (bytes_read <= 0) {
            if (bytes_read == 0) {
                // bytes_read = 0 : le client a fermé la connexion
                write_log(config.log_file, session.client_ip, session.client_port,
                          session.username, "DISCONNECT", "Client déconnecté proprement");
            } else {
                // bytes_read < 0 : erreur de lecture (connexion coupée, timeout, etc.)
                write_log(config.log_file, session.client_ip, session.client_port,
                          session.username, "DISCONNECT", "Erreur de lecture socket");
            }
            break;
        }
        
        // Termine la chaîne reçue par un caractère nul
        buffer[bytes_read] = '\0';
        
        // Nettoie les caractères de fin de ligne (\r\n ou \n)
        buffer[strcspn(buffer, "\r\n")] = 0;
        
        // ÉTAPE 3 : PARSING DE LA COMMANDE
        char *token = strtok(buffer, " \n");
        
        // Si au moins une commande a été reçue, traite-la
        if (token) {
            // COMMANDE AUTH : AUTHENTIFICATION
            if (strcmp(token, "AUTH") == 0) {
                // Récupère les deux paramètres : username et password_md5
                char *username = strtok(NULL, " \n");
                char *password_md5 = strtok(NULL, " \n");
                // Appelle le handler d'authentification
                auth(&session, username, password_md5, config.auth_file, config, conn_client);
            }
            
            // COMMANDE CREATE : CRÉATION DE BORDEREAU
            else if (strcmp(token, "CREATE") == 0) {
                // Récupère le paramètre : commande_id
                char *commande_id = strtok(NULL, " \n");
                if (commande_id) {
                    // Crée un nouveau bordereau pour cette commande
                    create(&session, commande_id, config, conn_client);
                } else {
                    // Paramètre manquant : envoie une erreur
                    char response[] = "ERROR MISSING_COMMANDE_ID\n";
                    send(session.client_socket, response, strlen(response), 0);
                    write_log(config.log_file, session.client_ip, session.client_port,
                              session.username, "CREATE", "Commande_id manquant");
                }
            }
            
            // COMMANDE STATUS : CONSULTATION DE STATUT
            else if (strcmp(token, "STATUS") == 0) {
                // Récupère le paramètre : numéro de bordereau
                char *bordereau = strtok(NULL, " \n");
                if (bordereau) {
                    // Consulte le statut du colis
                    status(&session, bordereau, config, conn_client);
                } else {
                    // Paramètre manquant : envoie une erreur
                    char response[] = "ERROR MISSING_BORDEREAU\n";
                    send(session.client_socket, response, strlen(response), 0);
                    write_log(config.log_file, session.client_ip, session.client_port,
                              session.username, "STATUS", "Bordereau manquant");
                }
            }
            
            // COMMANDE HELP : AIDE
            else if (strcmp(token, "HELP") == 0) {
                // Envoie le message d'aide au client
                help(&session);
                write_log(config.log_file, session.client_ip, session.client_port,
                          session.username, "HELP", "Commande HELP exécutée");
            }
            
            // COMMANDES QUIT/EXIT : DÉCONNEXION
            else if (strcmp(token, "QUIT") == 0 || strcmp(token, "EXIT") == 0) {
                // Envoie un message d'au revoir au client
                char response[] = "BYE\n";
                send(session.client_socket, response, strlen(response), 0);
                // Marque la connexion comme inactive pour sortir de la boucle
                connection_active = 0;
                write_log(config.log_file, session.client_ip, session.client_port,
                          session.username, "QUIT", "Déconnexion demandée par client");
            }
            
            // COMMANDE INCONNUE
            else {
                // Commande non reconnue : envoie une erreur
                char response[] = "ERROR UNKNOWN_COMMAND\n";
                send(session.client_socket, response, strlen(response), 0);
                write_log(config.log_file, session.client_ip, session.client_port,
                          session.username, "ERROR", token);
            }
        }
    }
    
    // ÉTAPE 4 : FERMETURE PROPRE DU PROCESSUS FILS
    
    // Ferme la connexion MySQL du fils (chaque fils a sa propre connexion)
    mysql_close(conn_client);
    
    // Ferme le socket client (libère le descripteur de fichier)
    close(session.client_socket);
    
    // Log de fin du processus fils
    write_log(config.log_file, session.client_ip, session.client_port,
              session.username, "EXIT", "Processus fils terminé");

    exit(EXIT_SUCCESS);
}

/**
 * main() - Point d'entrée du serveur Délivraptor multi-processus
 * 
 * Responsabilités principales:
 * 1. Initialisation du serveur
 *    - Graine du générateur aléatoire (rand seed)
 *    - Parsing de la configuration (getopt)
 *    - Connexion à la base de données MySQL (processus père)
 * 
 * 2. Configuration du gestionnaire de signaux
 *    - SIGCHLD pour éviter les processus zombies
 * 
 * 3. Création et configuration du socket serveur
 *    - Création d'un socket TCP (AF_INET = IPv4, SOCK_STREAM = TCP)
 *    - Option SO_REUSEADDR pour réutilisation rapide du port
 *    - Binding sur un port spécifique
 *    - Mise en mode écoute (listen)
 * 
 * 4. Boucle principale d'acceptation des connexions
 *    - Attend une connexion client (accept) - BLOQUANT
 *    - Fork() pour créer un processus fils
 *    - Processus père : ferme le socket client, continue à accepter
 *    - Processus fils : appelle gerer_client() et termine
 * 
 * 5. Clean (jamais atteint sauf arrêt forcé)
 *    - Ferme les sockets
 *    - Ferme la connexion MySQL
 *    - Log d'arrêt
 * 
 * Retour: int - statut de sortie (0 = succès, 1 = erreur)
 */
int main(int argc, char *argv[]) {
    srand(time(NULL));
    
    // ###############################################
    // 1. CONFIGURATION PAR DÉFAUT DU SERVEUR
    // ###############################################
    
    struct ServerConfig config = {
        .port =  PORT_SERVER_DEFAULT,
        .capacity = 5,
        .auth_file = "./auth.txt",
        .log_file = "./delivraptor.log"
    };
    
    // Variables globales pour accès par handlers de signaux
    global_config = &config;
    global_log_file = config.log_file;

    // ###############################################
    // 2. DÉFINITION DES OPTIONS GETOPT_LONG
    // ###############################################
    
    // Tableau des options longues (--option)
    // Chaque option a : nom, type (required_argument/no_argument), flag, valeur courte
    static struct option long_options[] = {
        {"port",     required_argument, 0, 'p'},
        {"capacity", required_argument, 0, 'c'},
        {"auth",     required_argument, 0, 'a'},
        {"log",      required_argument, 0, 'l'},
        {"help",     no_argument,       0, 'h'},
        {0, 0, 0, 0}
    };

    int opt;
    int option_index = 0;
    int has_errors = 0;
    
    // ###############################################
    // 3. PARSING DES OPTIONS EN LIGNE DE COMMANDE
    // ###############################################
    
    // getopt_long() parse les arguments -p, --port, -c, --capacity, etc.
    // Retourne -1 quand toutes les options ont été traitées
    while ((opt = getopt_long(argc, argv, "p:c:a:l:h", long_options, &option_index)) != -1) {
        switch (opt) {
            // OPTION PORT (-p / --port)
            case 'p':
                config.port = atoi(optarg);  // Convertit l'argument en entier
                // Validation : port doit être entre 1 et 65535
                if (config.port < 1 || config.port > 65535) {
                    fprintf(stderr, "ERREUR: Port invalide '%s'\n", optarg);
                    fprintf(stderr, "   Le port doit être entre 1 et 65535\n");
                    has_errors = 1;
                }
                break;
                
            // OPTION CAPACITÉ (-c / --capacity)
            case 'c':
                config.capacity = atoi(optarg);
                // Validation : capacité doit être positive
                if (config.capacity < 1) {
                    fprintf(stderr, "ERREUR: Capacité invalide '%s'\n", optarg);
                    fprintf(stderr, "   La capacité doit être un entier positif\n");
                    has_errors = 1;
                }
                break;
                
            case 'a':
                config.auth_file = optarg;
                if (access(config.auth_file, F_OK) == -1) {
                    fprintf(stderr, "AVERTISSEMENT: Fichier d'auth '%s' non trouvé\n", config.auth_file);
                }
                break;
                
            case 'l':
                config.log_file = optarg;
                break;
                
            case 'h':
                print_help(argv[0]);
                return EXIT_SUCCESS;
                
            case '?':
                fprintf(stderr, "\nUtilisez '%s --help' pour voir les options disponibles\n", argv[0]);
                return EXIT_FAILURE;
                
            default:
                fprintf(stderr, "ERREUR: Option inattendue '%c'\n", opt);
                return EXIT_FAILURE;
        }
    }
    
    if (has_errors) {
        fprintf(stderr, "\nImpossible de démarrer le serveur avec cette configuration\n");
        fprintf(stderr, "   Corrigez les erreurs ci-dessus ou utilisez '%s --help'\n", argv[0]);
        return EXIT_FAILURE;
    }
    
    // Configuration du handler SIGCHLD
    struct sigaction sa;
    sa.sa_handler = gerer_sigchld;
    sigemptyset(&sa.sa_mask);
    sa.sa_flags = SA_RESTART | SA_NOCLDSTOP;
    if (sigaction(SIGCHLD, &sa, NULL) == -1) {
        perror("sigaction");
        exit(EXIT_FAILURE);
    }
    
    // Initialisation base de données
    conn = config_BD();
    
    // Création et configuration du socket
    int server_fd = socket(AF_INET, SOCK_STREAM, 0);
    if (server_fd < 0) {
        perror("socket");
        mysql_close(conn);
        exit(EXIT_FAILURE);
    }
    
    // Permettre la réutilisation de l'adresse
    int optval = 1;
    setsockopt(server_fd, SOL_SOCKET, SO_REUSEADDR, &optval, sizeof(optval));
    
    struct sockaddr_in address;
    address.sin_family = AF_INET;
    address.sin_addr.s_addr = INADDR_ANY;
    address.sin_port = htons(config.port);

    if (bind(server_fd, (struct sockaddr*)&address, sizeof(address)) < 0) {
        perror("bind");
        close(server_fd);
        mysql_close(conn);
        exit(EXIT_FAILURE);
    }
    
    if (listen(server_fd, 10) < 0) {
        perror("listen");
        close(server_fd);
        mysql_close(conn);
        exit(EXIT_FAILURE);
    }
    
    // Log du démarrage
    char server_ip[INET_ADDRSTRLEN] = "0.0.0.0";
    log_server_start(config, server_ip);

    printf("Serveur Délivraptor en écoute sur le port %d\n", config.port);
    printf("Capacité maximale : %d colis\n", config.capacity);
    printf("Fichier d'authentification : %s\n", config.auth_file);
    printf("Fichier de logs : %s\n", config.log_file);
    printf("Mode : Multi-processus (fork)\n");
    
    // Boucle principale du serveur
    while (1) {
        struct sockaddr_in client_addr;
        socklen_t client_len = sizeof(client_addr);
        
        int client_socket = accept(server_fd, (struct sockaddr*)&client_addr, &client_len);
        if (client_socket < 0) {
            perror("accept");
            continue;
        }
        
        char client_ip[INET_ADDRSTRLEN];
        inet_ntop(AF_INET, &(client_addr.sin_addr), client_ip, INET_ADDRSTRLEN);
        int client_port = ntohs(client_addr.sin_port);
        
        // Création d'une session pour le client
        struct ClientSession session = {
            .client_socket = client_socket,
            .authentified = 0,
            .username = "",
            .client_port = client_port,
            .pid = 0
        };
        strncpy(session.client_ip, client_ip, INET_ADDRSTRLEN - 1);
        session.client_ip[INET_ADDRSTRLEN - 1] = '\0';
        
        write_log(config.log_file, client_ip, client_port,
                  NULL, "CONNECT", "Nouvelle connexion client");
        
        // Fork pour gérer le client dans un processus séparé
        pid_t pid = fork();
        
        if (pid < 0) {
            perror("fork");
            close(client_socket);
            write_log(config.log_file, client_ip, client_port,
                      NULL, "ERROR", "Échec du fork()");
        }
        else if (pid == 0) {
            // Processus fils
            close(server_fd); // Le fils n'a pas besoin du socket d'écoute
            
            // Fermer la connexion BDD du père
            if (conn) {
                mysql_close(conn);
                conn = NULL;
            }
            
            // Gérer le client
            gerer_client(session, config);
            exit(EXIT_SUCCESS);
        }
        else {
            session.pid = pid;
            close(client_socket);
            
            write_log(config.log_file, client_ip, client_port,
                      NULL, "FORK", "Fork terminé");
        }
    }
    
    // Cleanup
    mysql_close(conn);
    close(server_fd);
    log_server_stop(config, "0.0.0.0");
    
    return 0;
}