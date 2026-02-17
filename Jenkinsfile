pipeline {
    agent any

    environment {
        DOCKER_IMAGE = "boussofaye/redproduct-backend"
        DOCKER_TAG = "${env.BUILD_NUMBER}"
        DOCKER_HUB_CREDENTIALS = 'dockerhub-credentials' // ID de vos credentials Jenkins
    }

    stages {
        stage('Checkout') {
            steps {
                echo '🔍 Récupération du code source...'
                checkout scm
            }
        }

        stage('Build & Install') {
            steps {
                echo "🐳 Construction de l'image (Build #${DOCKER_TAG})..."
                // On build sans le --no-dev pour avoir PHPUnit disponible pour les tests
                sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."
                sh "docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest"
            }
        }

        stage('Run Tests') {
            steps {
                echo '🧪 Préparation de l\'environnement et exécution des tests...'
                /* On enchaîne les commandes dans le même container :
                   1. Créer le .env
                   2. Générer la clé (indispensable pour les Feature Tests)
                   3. Lancer les tests
                */
                sh """
                    docker run --rm ${DOCKER_IMAGE}:${DOCKER_TAG} sh -c "
                        cp .env.example .env && 
                        php artisan key:generate --env=testing && 
                        php artisan test --env=testing
                    "
                """
            }
        }

        

        stage('Push to Docker Hub') {
            steps {
                echo '🚀 Publication de l\'image sur Docker Hub...'
                script {
                    docker.withRegistry('', DOCKER_HUB_CREDENTIALS) {
                        docker.image("${DOCKER_IMAGE}:${DOCKER_TAG}").push()
                        docker.image("${DOCKER_IMAGE}:latest").push()
                    }
                }
            }
        }
    }

    post {
        always {
            echo '🧹 Nettoyage des images locales pour libérer de l\'espace...'
            sh "docker rmi ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest || true"
            sh "docker system prune -f"
        }
        success {
            echo '✅ Pipeline terminé avec succès !'
        }
        failure {
            echo '❌ Le pipeline a échoué. Vérifiez les logs ci-dessus.'
        }
    }
}