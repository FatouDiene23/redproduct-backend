pipeline {
    agent any
    
    environment {
        // Identifiants Docker Hub configurés dans Jenkins
        DOCKER_HUB_REG = credentials('dockerhub-credentials') 
        DOCKER_IMAGE = 'boussofaye/redproduct-backend'
        IMAGE_TAG = "${BUILD_NUMBER}"
    }
    
    stages {
        stage('Checkout') {
            steps {
                echo '🔍 Récupération du code source...'
                checkout scm
            }
        }
        
        stage('Install Dependencies') {
            steps {
                echo '📦 Installation des dépendances avec Composer...'
                // Utilisation de -u pour éviter les problèmes de droits (root vs jenkins)
                sh 'docker run --rm -u $(id -u):$(id -g) -v ${WORKSPACE}:/app -w /app composer:2.6 install --no-interaction --prefer-dist --no-scripts'
            }
        }
        
        stage('Run Tests') {
            steps {
                echo '🧪 Exécution des tests PHPUnit...'
                // On utilise le même conteneur pour garantir l'environnement
                sh 'docker run --rm -u $(id -u):$(id -g) -v ${WORKSPACE}:/app -w /app composer:2.6 php artisan test --env=testing'
            }
        }
        
        // stage('SonarQube Analysis') {
        //     steps {
        //         echo '🔍 Analyse SonarQube...'
        //         script {
        //             def scannerHome = tool 'SonarQube Scanner'
        //             withSonarQubeEnv('SonarQube') {
        //                 sh "${scannerHome}/bin/sonar-scanner"
        //             }
        //         }
        //     }
        // }
        
        stage('Build Docker Image') {
            steps {
                echo '🐳 Construction de l\'image Docker...'
                sh '''
                    docker build -t ${DOCKER_IMAGE}:${IMAGE_TAG} .
                    docker tag ${DOCKER_IMAGE}:${IMAGE_TAG} ${DOCKER_IMAGE}:latest
                '''
            }
        }
        
        stage('Trivy Scan') {
            steps {
                echo '🔒 Scan de sécurité Trivy...'
                sh '''
                    docker run --rm -v /var/run/docker.sock:/var/run/docker.sock \
                    aquasec/trivy image --severity HIGH,CRITICAL \
                    ${DOCKER_IMAGE}:${IMAGE_TAG} || true
                '''
            }
        }
        
        stage('Push to Docker Hub') {
            steps {
                echo '🚀 Push vers Docker Hub...'
                script {
                    // Utilisation de la méthode recommandée pour le login
                    withCredentials([usernamePassword(credentialsId: 'dockerhub-credentials', passwordVariable: 'PASS', usernameVariable: 'USER')]) {
                        sh "echo ${PASS} | docker login -u ${USER} --password-stdin"
                        sh "docker push ${DOCKER_IMAGE}:${IMAGE_TAG}"
                        sh "docker push ${DOCKER_IMAGE}:latest"
                        sh "docker logout"
                    }
                }
            }
        }
    }
    
    post {
        success {
            echo '✅ Pipeline réussi!'
            echo "🐳 Image disponible : ${DOCKER_IMAGE}:${IMAGE_TAG}"
        }
        failure {
            echo '❌ Pipeline échoué'
            echo 'Vérifiez les logs pour corriger l\'erreur.'
        }
        always {
            echo '🧹 Nettoyage des images intermédiaires...'
            sh 'docker system prune -f || true'
        }
    }
}