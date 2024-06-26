<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Entity\UsersCard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//Encrypt password

/**
 * @Route("/api", name="api_")
 */
class UserController extends AbstractController
{

    private $entityManager;
    private $created_at;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->created_at = new \DateTime();
        $this->created_at->setTimezone(new \DateTimeZone('GMT-3'));
    }

    /**
     * @Route("/user-login", name="user_login")
     */
    public function userLogin(UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();

        $document = (isset($_POST['document'])) ? $_POST['document'] : null;
        $password = (isset($_POST['password'])) ? $_POST['password'] : null;

        if($document != null){
            $user = $this->entityManager->getRepository(User::class)->findBy(['document' => $document]);
        }


        if(!empty($user[0])){

            if (!$passwordHasher->isPasswordValid($user[0], $password)) {
                return $this->json('El documento o contraseña no coincide.', 202, ["Content-Type" => "application/json"]);
            }

            $hashedPassword = $passwordHasher->hashPassword(
                $user[0],
                $password
            );

            $token = $this->generarTokenLogin();

            $user[0]->setTokenLogin($token);

            $this->entityManager->persist($user[0]);
            $this->entityManager->flush();

            return $this->json($token, 200, ["Content-Type" => "application/json"]);

        }else{

            return $this->json('Este documento no pertenece a ningún usuario.', 202, ["Content-Type" => "application/json"]);


        }
        

    }


    /**
     * @Route("/user-register", name="user_register")
     */
    public function userRegister(UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();

        $name = (isset($_POST['name'])) ? $_POST['name'] : null;
        $subname = (isset($_POST['subname'])) ? $_POST['subname'] : null;
        $document = (isset($_POST['document'])) ? $_POST['document'] : null;
        $password = (isset($_POST['password'])) ? $_POST['password'] : null;

        if($document == null){
            return $this->json('Error, debe completar el documento y contraseña.', 202, ["Content-Type" => "application/json"]);
        }

        $user_exist = $this->entityManager->getRepository(User::class)->findBy(['document' => $document]);

        if(isset($user_exist[0])){
            return $this->json('Error, el documento ya se encuentra registrado.', 202, ["Content-Type" => "application/json"]);
        }

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setName(strtoupper(trim($name)));
        $user->setSubname(strtoupper(trim($subname)));
        $user->setDocument($document);
        $user->setPassword($hashedPassword);
        $user->setRoles(["USER"]);
        $user->setCreatedAt($this->created_at);
        $user->setActive(1);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/user-update", name="user_update")
     */
    public function userUpdate(): JsonResponse
    {

        $name = (isset($_POST['name'])) ? $_POST['name'] : null;
        $subname = (isset($_POST['subname'])) ? $_POST['subname'] : null;
        $id = (isset($_POST['id'])) ? $_POST['id'] : null;

        if($id === null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        $user->setName(strtoupper(trim($name)));
        $user->setSubname(strtoupper(trim($subname)));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json('Ok', 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/user-change-state", name="user_change_state")
     */
    public function userChangeState(): JsonResponse
    {

        $id = (isset($_POST['id'])) ? $_POST['id'] : null;
        $active = (isset($_POST['active'])) ? $_POST['active'] : null;

        if($id == null){
            return $this->json('Error, el id no es válido.', 400, ["Content-Type" => "application/json"]);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        $user->setActive($active);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json('Cliente eliminado.', 200, ["Content-Type" => "application/json"]);

    }

    private function generarTokenLogin(){

        $texto="AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";

        $largo=strlen($texto);

        $letras="";

        $contador=1;

        while($contador<=20){
            $azar=rand(1,$largo);
            $posicion=$azar-1;

            //Substr(Variable Texto,Posicion, Cantidad)

            $caracter=Substr($texto,$posicion,1);

            $letras=$letras.$caracter;

            $contador++;

        }


        return $letras;
    }


    /**
     * @Route("/get-user", name="get_user")
     */
    public function getUserByToken(): JsonResponse
    {
        $token = (isset($_POST['token'])) ? $_POST['token'] : null;

        if($token == null){
            return $this->json('Error, debe enviar el token como parámetro.', 400, ["Content-Type" => "application/json"]);
        }


        $data = $this->entityManager->getRepository(User::class)->findBy(['tokenLogin' => $token]);

        $user = [];

        /**
         * @var $u User
         */

        foreach ($data as $u) {

            $user['id'] = $u->getId();
            $user['document'] = $u->getDocument();
            $user['name'] = $u->getName();
            $user['subname'] = (($u->getSubname()) ? ' ' . $u->getSubname() : '');
            $user['rol'] = $u->getRoles()[0];
        }

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/get-users-list", name="get_users_list")
     */
    public function getUsersList(): JsonResponse
    {

        $data = $this->entityManager->getRepository(User::class)->findAll();

        $user = [];

        /**
         * @var $u User
         */

        foreach ($data as $u) {

            $document = $u->getDocument();
            $primeraParte = substr($document,0,1);
            $segundaParte = substr($document,1,3);
            $terceraParte = substr($document,4,3);
            $cuartaParte = substr($document,7,1);

            //Solo almaceno los usuarios que contengan el rol de USER
            if (in_array('USER', $u->getRoles(), true) && $u->isActive()) {
                $user[] = array(
                    'id' => $u->getId(),
                    'document' => $primeraParte.'.'.$segundaParte.'.'.$terceraParte.'-'.$cuartaParte,
                    'name' => $u->getName(),
                    'subname' => (($u->getSubname()) ? $u->getSubname() : ''),
                    'active' => $u->isActive()
                );
            }

        }

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/get-user-id", name="get_user_id")
     */
    public function getUser(): JsonResponse
    {

        $id = (isset($_POST['id'])) ? $_POST['id'] : null;

        if($id === null){
            return $this->json('Error, debe enviar el id como parámetro.', 202, ["Content-Type" => "application/json"]);
        }

        $user = [];
        $user_class = $this->entityManager->getRepository(User::class)->find($id);

        $document = $user_class->getDocument();
        $primeraParte = substr($document,0,1);
        $segundaParte = substr($document,1,3);
        $terceraParte = substr($document,4,3);
        $cuartaParte = substr($document,7,1);

        //Solo almaceno los usuarios que contengan el rol de USER
        if (in_array('USER', $user_class->getRoles(), true)) {
            $user = array(
                'id' => $user_class->getId(),
                'document' => $primeraParte.'.'.$segundaParte.'.'.$terceraParte.'-'.$cuartaParte,
                'name' => $user_class->getName(),
                'subname' => (($user_class->getSubname()) ? $user_class->getSubname() : '')
            );
        }

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/get-cards-list", name="get_cards_list")
     */
    public function getCardsList(): JsonResponse
    {

        $data = $this->entityManager->getRepository(Card::class)->findAll();

        $card = [];

        /**
         * @var $c Card
         */

        foreach ($data as $c) {

            if($c->isActive()){
                $card[] = array(
                    'id' => $c->getId(),
                    'title' => $c->getTitle(),
                    'desc' => (!empty($c->getDescription()) ? $c->getDescription() : ''),
                    'duration' => $c->getDaysLimit(),
                    'stars' => $c->getStars(),
                    'type' => $c->getType(),
                );
            }

        }

        return $this->json($card, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/get-cards-users", name="get_cards_users")
     */
    public function getCardsUsers(): JsonResponse
    {
        $client_id = (isset($_POST['client_id'])) ? $_POST['client_id'] : null;

        if($client_id == null){
            return $this->json('Error, el id del cliente no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $card = [];
        $data = $this->entityManager->getRepository(Card::class)->getCardsUsers($this->entityManager,$client_id);

        /**
         * @var $c Card
         */

        foreach ($data as $c) {

            if($c->isActive()){
                $card[] = array(
                    'id' => $c->getId(),
                    'title' => $c->getTitle(),
                    'desc' => (!empty($c->getDescription()) ? $c->getDescription() : ''),
                    'duration' => $c->getDaysLimit(),
                    'stars' => $c->getStars(),
                    'type' => $c->getType()
                );
            }

        }

        return $this->json($card, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/get-user-cards-list", name="get_user_cards_list")
     */
    public function getUserCardsList(): JsonResponse
    {

        $client_id = (isset($_POST['client_id'])) ? $_POST['client_id'] : null;

        $data = $this->entityManager->getRepository(UsersCard::class)->findBy(['user'=>$client_id]);

        $card = [];

        $date_now = strtotime($this->created_at->format('Y-m-d'));


        /**
         * @var $c UsersCard
         */

        foreach ($data as $c) {

            if($c->getCard()->isActive()){
                $date_card = $c->getCreatedAt()->format('Y-m-d');

                $date_append = strtotime(date("d-m-Y",strtotime($date_card."+ ". $c->getCard()->getDaysLimit() ." days")));

                $datediff = $date_append - $date_now;

                $card[] = array(
                    'id_assigned' => $c->getId(),
                    'client_name' => $c->getUser()->getName() . (($c->getUser()->getSubname()) ? ' ' . $c->getUser()->getSubname() : ''),
                    'title' => $c->getCard()->getTitle(),
                    'desc' => (!empty($c->getCard()->getDescription()) ? $c->getCard()->getDescription() : ''),
                    'duration' => $c->getCard()->getDaysLimit(),
                    'stars' => $c->getCard()->getStars(),
                    'type' => $c->getCard()->getType(),
                    'marked_stars' => $c->getStars(),
                    'date_create' => $c->getCreatedAt()->format('d/m/Y'),
                    'datediff' => round($datediff / (60 * 60 * 24)),
                    'date_valide' => date("d/m/Y",$date_append),
                    'message_giftcard' => $c->getGiftcardMessage(),
                    'status_giftcard' => $c->isGiftcardStatus()
                );
            }

        }

        return $this->json($card, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/card-update", name="card_update")
     */
    public function cardUpdate(): JsonResponse
    {

        $id = (isset($_POST['id'])) ? $_POST['id'] : null;
        $title = (isset($_POST['title'])) ? $_POST['title'] : null;
        $desc = (isset($_POST['desc'])) ? $_POST['desc'] : null;
        $type = (isset($_POST['type'])) ? $_POST['type'] : null;
        $duration = (isset($_POST['duration'])) ? $_POST['duration'] : null;
        $stars = (isset($_POST['stars'])) ? $_POST['stars'] : null;

        if($id == null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $card = $this->entityManager->getRepository(Card::class)->find($id);

        $card->setTitle(trim($title));
        $card->setDescription(trim($desc));
        $card->setDaysLimit($duration);
        $card->setStars($stars);
        $card->setType($type);

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $this->json($card->getId(), 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/card-delete", name="card_delete")
     */
    public function cardDelete(): JsonResponse
    {

        $id = (isset($_POST['id'])) ? $_POST['id'] : null;

        if($id == null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $card = $this->entityManager->getRepository(Card::class)->find($id);
        $card->setActive(0);


        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $this->json('Tarjeta eliminada.', 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/card-create", name="card_create")
     */
    public function cardCreate(): JsonResponse
    {

        $title = (isset($_POST['title'])) ? $_POST['title'] : null;
        $desc = (isset($_POST['desc'])) ? $_POST['desc'] : null;
        $type = (isset($_POST['type'])) ? $_POST['type'] : null;
        $duration = (isset($_POST['duration'])) ? $_POST['duration'] : null;
        $stars = (isset($_POST['stars'])) ? $_POST['stars'] : null;

        if($title == null){
            return $this->json('Error, el título no puede estar vacío.', 202, ["Content-Type" => "application/json"]);
        }

        if($duration == null){
            return $this->json('Error, la duración no puede estar vacío.', 202, ["Content-Type" => "application/json"]);
        }

        if($stars == null){
            return $this->json('Error, el número de estrellas no puede estar vacío.', 202, ["Content-Type" => "application/json"]);
        }

        if($type == null){
            return $this->json('Error, el tipo de tarjeta no puede estar vacío.', 202, ["Content-Type" => "application/json"]);
        }

        $card = new Card();

        $card->setTitle(trim($title));
        $card->setDescription(trim($desc));
        $card->setType($type);
        $card->setDaysLimit($duration);
        $card->setStars($stars);
        $card->setActive(1);

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $this->json($card, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/card-assign", name="card_assign")
     */
    public function cardAssign(): JsonResponse
    {

        $card_id = (isset($_POST['card_id'])) ? $_POST['card_id'] : null;
        $client_id = (isset($_POST['client_id'])) ? $_POST['client_id'] : null;

        if($card_id == null || $client_id == null){
            return $this->json('Error, verifique los datos enviados.', 202, ["Content-Type" => "application/json"]);
        }

        $card = $this->entityManager->getRepository(Card::class)->find($card_id);
        $client = $this->entityManager->getRepository(User::class)->find($client_id);

        $new_assign = new UsersCard();
        $new_assign->setCard($card);
        $new_assign->setUser($client);
        $new_assign->setStars(0);
        $new_assign->setGiftcardStatus(0);
        $new_assign->setCreatedAt($this->created_at);

        $this->entityManager->persist($new_assign);
        $this->entityManager->flush();

        return $this->json($new_assign->getId(), 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/number-card-update", name="number_card_update")
     */
    public function numberCardUpdate(): JsonResponse
    {

        $id_assigned = (isset($_POST['id_assigned'])) ? $_POST['id_assigned'] : null;
        $position = (isset($_POST['position'])) ? $_POST['position'] : null;

        if($id_assigned == null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        if($position == null){
            return $this->json('Error, el número de las estrellas no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $user_card = $this->entityManager->getRepository(UsersCard::class)->find($id_assigned);

        $user_card->setStars($position);

        $this->entityManager->persist($user_card);
        $this->entityManager->flush();

        return $this->json($position, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/card-assigned-delete", name="card_assigned_delete")
     */
    public function cardAssignedDelete(): JsonResponse
    {

        $id_assigned = (isset($_POST['id_assigned'])) ? $_POST['id_assigned'] : null;

        if($id_assigned == null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $card = $this->entityManager->getRepository(UsersCard::class)->find($id_assigned);

        $this->entityManager->remove($card);
        $this->entityManager->flush();

        return $this->json('Tarjeta eliminada.', 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/update-message-giftcard", name="update_message_giftcard")
     */
    public function updateMessageGiftcard(): JsonResponse
    {

        $id_assigned = (isset($_POST['id_assigned'])) ? $_POST['id_assigned'] : null;
        $message = (isset($_POST['message'])) ? $_POST['message'] : null;

        if($id_assigned == null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $card = $this->entityManager->getRepository(UsersCard::class)->find($id_assigned);
        $card->setGiftcardMessage($message);

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $this->json($card->getId(), 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/update-used-giftcard", name="update_used_giftcard")
     */
    public function updateUsedGiftcard(): JsonResponse
    {

        $id_assigned = (isset($_POST['id_assigned'])) ? $_POST['id_assigned'] : null;

        if($id_assigned == null){
            return $this->json('Error, el id no es válido.', 202, ["Content-Type" => "application/json"]);
        }

        $card = $this->entityManager->getRepository(UsersCard::class)->find($id_assigned);
        $card->setGiftcardStatus(1);

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $this->json($card->getId(), 200, ["Content-Type" => "application/json"]);

    }


}
