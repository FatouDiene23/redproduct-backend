pipeline {
    agent any
    
    environment {
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
        
        // On fusionne l'install et le build car votre Dockerfile s'en occupe
        stage('Build & Install') {
            steps {
                echo '🐳 Construction de l\'image (inclut composer install)...'
                sh '''
                    docker build -t ${DOCKER_IMAGE}:${IMAGE_TAG} .
                    docker tag ${DOCKER_IMAGE}:${IMAGE_TAG} ${DOCKER_IMAGE}:latest
                '''
            }
        }
        
        stage('Run Tests') {
            steps {
                echo '🧪 Exécution des tests dans l\'image buildée...'
                // On lance les tests à l'intérieur de l'image que l'on vient de construire
                sh "docker run --rm ${DOCKER_IMAGE}:${IMAGE_TAG} php artisan test --env=testing"
            }
        }
        
        stage('Trivy Scan') {
            steps {
                echo '🔒 Scan de sécurité Trivy...'
                sh "docker run --rm -v /var/run/docker.sock:/var/run/docker.sock aquasec/trivy image --severity HIGH,CRITICAL ${DOCKER_IMAGE}:${IMAGE_TAG} || true"
            }
        }
        
        stage('Push to Docker Hub') {
            steps {
                echo '🚀 Push vers Docker Hub...'
                script {
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
        }
        failure {
            echo '❌ Pipeline échoué'
        }
        always {
            echo '🧹 Nettoyage...'
            sh 'docker system prune -f || true'
        }
    }
}