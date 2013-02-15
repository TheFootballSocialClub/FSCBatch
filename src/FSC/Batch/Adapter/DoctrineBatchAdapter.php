<?php

namespace FSC\Batch\Adapter;

use Pagerfanta\Adapter\AdapterInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * This adapter, will traverse the table with range queries on the id, instead of LIMIT/OFFSET.
 *
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class DoctrineBatchAdapter implements AdapterInterface
{
    protected $queryBuilder;
    protected $identifierField;

    protected $lastId;
    protected $maxId;

    public function __construct(QueryBuilder $queryBuilder, $identifierField = 'id')
    {
        $this->queryBuilder = $queryBuilder;
        $this->identifierField = $identifierField;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $qb = $this->cloneQueryBuilder($this->queryBuilder);

        $qb->select($qb->expr()->count($qb->getRootAlias()));
        $qb->setFirstResult(null);
        $qb->setMaxResults(null);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        if (null === $this->maxId) {
            $this->maxId = $this->getMaxId();
        }

        if (null === $this->lastId) {
            $this->lastId = $this->getMinId() + $offset;
        }

        if (($offset + $length) > $this->maxId) {
            return null; // Will end the batch
        }

        $qb = $this->cloneQueryBuilder($this->queryBuilder);
        $qb ->andWhere($qb->expr()->gte(sprintf('%s.%s', $qb->getRootAlias(), $this->identifierField), ':entity_id'))
            ->setParameter('entity_id', $this->lastId);
        $qb->setMaxResults($length);

        $slice = $qb->getQuery()->getResult();

        if (count($slice)) {
            $lastObject = end($slice);
            $this->lastId = $lastObject->{'get'.ucfirst($this->identifierField)}();
        } else {
            // If there was no result, we increase the next id to start from, to make sure we progress
            $this->lastId += $length;
        }

        return $slice;
    }

    protected function getMaxId()
    {
        $qb = $this->cloneQueryBuilder($this->queryBuilder);

        $qb->select($qb->expr()->max(sprintf('%s.%s', $qb->getRootAlias(), $this->identifierField)));

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function getMinId()
    {
        $qb = $this->cloneQueryBuilder($this->queryBuilder);

        $qb->select($qb->expr()->min(sprintf('%s.%s', $qb->getRootAlias(), $this->identifierField)));

        return $qb->getQuery()->getSingleScalarResult();
    }

    private function cloneQueryBuilder(QueryBuilder $qb)
    {
        $cloneQb = clone $qb;
        $cloneQb->setFirstResult(null);
        $cloneQb->setMaxResults(null);
        $cloneQb->setParameters($qb->getParameters());
        foreach ($qb->getHints() as $name => $value) {
            $cloneQb->setHint($name, $value);
        }

        return $cloneQb;
    }
}
