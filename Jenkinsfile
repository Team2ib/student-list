pipeline {
	environment {
		JENKINS_CONTAINER_NETWORK = "jenkins_default"
		IMAGE_NAME = "student_list"
                FRONT_CONTAINER_NAME = "website"
		IMAGE_TAG = "latest"
		IMAGE_REPO = "team2"
		IMAGE_REGISTRY = credentials('registry_url')
                STUDENT_LIST_LOGIN = credentials('student_list_login')
	}
	agent none
	stages {
                stage('Unit Testing Python App') {
                        agent {
                                docker {
                                                image 'python:2.7-stretch'
                                }
                        }
			environment {
				student_age_file_path = ${PWD}"/student_age.json"
			}
                        steps {
                                script {
                                        sh '''
					cd simple_api
					apt-get update -y
  					apt-get install -y \
  					  python-dev=2.7.13-2 \
  				 	  python3-dev=3.5.3-1 \
  					  libsasl2-dev=2.1.27* \
  					  libldap2-dev=2.4.44* \
  					  libssl-dev=1.1.0l*
  					pip install --no-cache-dir -r requirements.txt
					echo $student_age_file_path
					python -m unittest discover -s . -p 'tests.py'
					'''
                                }
                        }
                }
                stage('Test Dockerfile with Hadolint linter') {
                        agent {
                                docker {
                                                image 'hadolint/hadolint:latest-debian'
                                }
                        }
                        steps {
                                script {
                                        sh 'hadolint simple_api/Dockerfile'
                                }
                        }
                }
                stage('Test Yaml with Yamllint linter') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh 'docker run --rm -v $(pwd):/data cytopia/yamllint .'
                                }
                        }
                }
                stage('Test Playbooks with Ansible-lint linter') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh '''
					docker run --rm -v $(pwd):/data cytopia/ansible-lint ansible/setup-dependencies.yml
					docker run --rm -v $(pwd):/data cytopia/ansible-lint ansible/portainer-agent.yml
					docker run --rm -v $(pwd):/data cytopia/ansible-lint ansible/student_list.yml
					'''
                                }
                        }
                }
		stage('Build Image') {
			agent {
				docker {
						image 'docker:dind'
				}
			}
			steps {
				script {
					sh '''
					docker build -t $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:${GIT_COMMIT} simple_api
					docker tag $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:${GIT_COMMIT} $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:${IMAGE_TAG}
					'''
				}
			}
		}
                stage('Run Api Container') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh '''
					docker rm -vf ${IMAGE_NAME} || echo 0
					docker run -d -v ${PWD}/simple_api/student_age.json:/data/student_age.json --network $JENKINS_CONTAINER_NETWORK --name $IMAGE_NAME $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:$IMAGE_TAG 
					'''
                                }
                        }
                }
                stage('Test Api Container') {
                        agent any
                        steps {
                                script {
                                        sh '''
					curl -u ${STUDENT_LIST_LOGIN_USR}:${STUDENT_LIST_LOGIN_PSW} http://$IMAGE_NAME:5000/pozos/api/v1.0/get_student_ages | grep -q "student_ages"
                                        '''
                                }
                        }
                }
                stage('Run Website Container') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh '''
					docker rm -vf ${FRONT_CONTAINER_NAME} || echo 0
					docker run -d --network $JENKINS_CONTAINER_NETWORK -e USERNAME=${STUDENT_LIST_LOGIN_USR} -e PASSWORD=${STUDENT_LIST_LOGIN_PSW} -v ${PWD}/website:/var/www/html --name $FRONT_CONTAINER_NAME php:apache
					'''
                                }
                        }
                }
		stage('Test Website Container') {
			agent any
			steps {
				script {
					sh '''
					curl http://$FRONT_CONTAINER_NAME | grep -q "Student Checking App"
					'''
				}
			}
		}
		stage('Clean Container') {
			agent any
			steps {
				script {
					sh '''
					docker rm -vf ${IMAGE_NAME}
                                        docker rm -vf ${FRONT_CONTAINER_NAME}
					'''
				}
			}
		}
		stage('Push Image on Private Registry') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
			environment {
				PORTUS_SECRET = credentials('portus_secret')
			}
			steps {
				script {
					sh '''
					docker login -u $PORTUS_SECRET_USR -p $PORTUS_SECRET_PSW $IMAGE_REGISTRY
					docker push $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:${GIT_COMMIT}
					docker push $IMAGE_REGISTRY/$IMAGE_REPO/$IMAGE_NAME:${IMAGE_TAG}
					'''
				}
			}
		}
		stage('Ansible Deploy Staging') {
			agent { 
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest'
					args '-u root'
				} 
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
				ANSIBLE_VAULTPASS = credentials('ansible_vaultpass')
			}
			steps {
				script {
					sh '''
					cd ansible
					cp \$SSH_SECRET id_rsa
					chmod 600 id_rsa
					cp \$ANSIBLE_VAULTPASS .vault_pass
					ansible-playbook -i staging.yml setup-dependencies.yml --private-key id_rsa --vault-password-file=.vault_pass
					'''
					sh '''
					cd ansible
					ansible-playbook -i staging.yml portainer-agent.yml --private-key id_rsa --vault-password-file=.vault_pass
					ansible-playbook -i staging.yml student_list.yml --private-key id_rsa --vault-password-file=.vault_pass
					'''
				}
			}
		}
		stage('Test Staging Deployment') {
			agent {
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest'
					args '-u root'					
				}
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
				ANSIBLE_VAULTPASS = credentials('ansible_vaultpass')
			}
			steps {
				script {
					sh '''
					cd ansible
					cp \$SSH_SECRET id_rsa
					chmod 600 id_rsa
					cp \$ANSIBLE_VAULTPASS .vault_pass
					ansible-playbook -i staging.yml tests.yml --private-key id_rsa --vault-password-file=.vault_pass
					'''
				}
			}
		}
		stage('Ansible Deploy Production') {
			agent { 
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest'
					args '-u root'
				} 
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
				ANSIBLE_VAULTPASS = credentials('ansible_vaultpass')
			}
			steps {
				script {
					sh '''
					cd ansible
					cp \$SSH_SECRET id_rsa
					chmod 600 id_rsa
					cp \$ANSIBLE_VAULTPASS .vault_pass
					ansible-playbook -i production.yml setup-dependencies.yml --private-key id_rsa --vault-password-file=.vault_pass
					'''
					sh '''
					cd ansible
					ansible-playbook -i production.yml portainer-agent.yml --private-key id_rsa --vault-password-file=.vault_pass					
					ansible-playbook -i production.yml student_list.yml --private-key id_rsa --vault-password-file=.vault_pass
					'''
				}
			}
		}
		stage('Test Production Deployment') {
			agent {
				docker {
					image 'registry.gitlab.com/robconnolly/docker-ansible:latest'
					args '-u root'
				}
			}
			environment {
				SSH_SECRET = credentials('ssh_private_key')
				ANSIBLE_VAULTPASS = credentials('ansible_vaultpass')
			}
			steps {
				script {
					sh '''
					cd ansible
					cp \$SSH_SECRET id_rsa
					chmod 600 id_rsa
					cp \$ANSIBLE_VAULTPASS .vault_pass
					ansible-playbook -i production.yml tests.yml --private-key id_rsa --vault-password-file=.vault_pass
					'''
				}
			}
		}
                stage('Docker Image/Volume Cleanup') {
                        agent {
                                docker {
                                                image 'docker:dind'
                                }
                        }
                        steps {
                                script {
                                        sh '''
					docker volume prune -f
					docker image prune -f
					docker network prune -f
					'''
                                }
                        }
                }
	}
}
