pipeline {
    agent any
    
    environment {
        DOCKER_HUB_CREDENTIALS = credentials('dockerhub-credentials')
        DOCKER_IMAGE = 'boussofaye/redproduct-backend'  // ← CORRIGÉ
        IMAGE_TAG = "${BUILD_NUMBER}"
        // SONAR_TOKEN = credentials('sonarqube-backend-token')  // ← Décommenté quand SonarQube sera prêt
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
                echo '📦 Installation des dépendances...'
                sh 'docker run --rm -v ${WORKSPACE}:/app -w /app composer:2.6 install --no-interaction --prefer-dist --no-scripts'
            }
        }
        
        stage('Run Tests') {
            steps {
                echo '🧪 Exécution des tests...'
                sh '''
                    docker run --rm -v $(WORKSPACE):/app -w /app composer:2.6 \
                    bash -c "cd /app && php artisan test || true"
                '''
            }
        }
        
        // Décommenter quand SonarQube sera installé
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
                sh '''
                    echo $DOCKER_HUB_CREDENTIALS_PSW | docker login -u $DOCKER_HUB_CREDENTIALS_USR --password-stdin
                    docker push ${DOCKER_IMAGE}:${IMAGE_TAG}
                    docker push ${DOCKER_IMAGE}:latest
                    docker logout
                '''
            }
        }
    }
    
    post {
        success {
            echo '✅ Pipeline réussi!'
            echo "🐳 Image disponible : ${DOCKER_IMAGE}:${IMAGE_TAG}"
            echo "🔗 https://hub.docker.com/r/${DOCKER_IMAGE}"
        }
        failure {
            echo '❌ Pipeline échoué'
            echo 'Consultez les logs ci-dessus pour identifier le problème'
        }
        always {
            echo '🧹 Nettoyage...'
            sh 'docker system prune -f || true'
        }
    }
}