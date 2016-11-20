<?php


abstract class Document {

    /**
     * @MongoDB\Boolean
     */
    protected $isErasmus; // Le contenu est-il lié à un projet Erasmus ?

    /**
     * @MongoDB\String
     */
    protected $language; // La langue du document

    /**
     * @MongoDB\ReferenceOne(targetDocument="TheScienceTour\ChallengeBundle\Document\Challenge", inversedBy="translations")
     */
    protected $principal; // Le document original

    /**
     * @MongoDB\ReferenceMany(targetDocument="TheScienceTour\ChallengeBundle\Document\Challenge", mappedBy="principal")
     */
    protected $translations; // Ensemble des traductions


    public function getIsErasmus() {
		return $this->isErasmus;
	}

	public function getLanguage() {
		return $this->isErasmus;
	}

	public function getPrincipal() {
		return $this->isErasmus;
	}

	public function setIsErasmus($isErasmus) {
		$this->isErasmus = $isErasmus;
		return $this;
	}

	public function setLanguage($language) {
		$this->language = $language;
		return $this;
	}

	public function setPrincipal(\TheScienceTour\DocumentBundle\Document\Document $principal) {
		$this->principal = $principal;
		return $this;
	}

	public function getTranslations() {
		return $this->translations;
    }

	public function addTranslation(\TheScienceTour\DocumentBundle\Document\Document $translation) {
		$this->translations[] = $translation;
	}

	public function removeTranslation(\TheScienceTour\DocumentBundle\Document\Document $translation) {
		$this->translations->removeElement($translation);
	}

}
