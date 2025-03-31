<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SodiumException;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_project_title', fields: ['title'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_project_uuid', fields: ['uuid'])]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $uuid = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $gitUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $gitUsername = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $gitTokenEncrypted = null;
    private ?string $gitToken = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'projects')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo ? $this->logo : 'medias/logo/logo.png';
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getGitUrl(): ?string
    {
        return $this->gitUrl;
    }

    public function setGitUrl(?string $gitUrl): static
    {
        $this->gitUrl = $gitUrl;

        return $this;
    }

    public function getGitUsername(): ?string
    {
        return $this->gitUsername;
    }

    public function setGitUsername(?string $gitUsername): static
    {
        $this->gitUsername = $gitUsername;

        return $this;
    }


    public function setGitToken(?string $gitToken): self
    {
        $this->gitToken = $gitToken;
        $this->gitTokenEncrypted = $gitToken ? self::encrypt($gitToken) : null;
        return $this;
    }

    public function getGitToken(): ?string
    {
        if ($this->gitToken === null && $this->gitTokenEncrypted !== null) {
            $this->gitToken = self::decrypt($this->gitTokenEncrypted);
        }
        return $this->gitToken;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addProject($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeProject($this);
        }

        return $this;
    }


    private static function encrypt(string $plainText): string
    {
        dump($_ENV['APP_ENCRYPT_KEY']);
        $key = base64_decode($_ENV['APP_ENCRYPT_KEY']);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plainText, $nonce, $key);
        return base64_encode($nonce . $ciphertext);
    }

    private static function decrypt(string $encryptedText): ?string
    {
        $key = base64_decode($_ENV['APP_ENCRYPT_KEY']);
        $decoded = base64_decode($encryptedText);
        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        
        try {
            return sodium_crypto_secretbox_open($ciphertext, $nonce, $key) ?: null;
        } catch (SodiumException) {
            return null;
        }
    }    
}
