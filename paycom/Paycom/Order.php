<?php

namespace Paycom;

/**
 * Class Order
 *
 * Example MySQL table might look like to the following:
 *
 * CREATE TABLE orders
 * (
 *     id          INT AUTO_INCREMENT PRIMARY KEY,
 *     product_ids VARCHAR(255)   NOT NULL,
 *     amount      DECIMAL(18, 2) NOT NULL,
 *     state       TINYINT(1)     NOT NULL,
 *     user_id     INT            NOT NULL,
 *     phone       VARCHAR(15)    NOT NULL
 * ) ENGINE = InnoDB;
 *
 */
class Order extends Database
{
    /** Order is available for sell, anyone can buy it. */
    const STATE_AVAILABLE = 0;

    /** Pay in progress, order must not be changed. */
    const STATE_WAITING_PAY = 1;

    /** Order completed and not available for sell. */
    const STATE_PAY_ACCEPTED = 2;

    /** Order is cancelled. */
    const STATE_CANCELLED = 3;

    public $request_id;
    public $params;

    // todo: Adjust Order specific fields for your needs

    /**
     * Order ID
     */
    public $id;

    /**
     * Total price of the selected products/services
     */
    public $total_price;

    /**
     * State of the order
     */
    public $paid;

    public function __construct($request_id)
    {
        $this->request_id = $request_id;
    }

    
	
	/**
    * Validate checkPerformTransaction. Also, this validation function is used to validate createTransaction,
    * when a transaction is not found in a db.
    */
    public function validateTransaction(array $params)
    {        
        // check, whether total_price is numeric or not
        if (!is_numeric($params['amount'])) {
            throw new PaycomException(
                $this->request_id,
                'Incorrect amount.',
                PaycomException::ERROR_INVALID_AMOUNT
            );
        }

        // check, whether order_id is available or not
        if (!isset($params['account']['order_id']) || !$params['account']['order_id']) {
            throw new PaycomException(
                $this->request_id,
                PaycomException::message(
                    'Неверный код заказа.',
                    'Harid kodida xatolik.',
                    'Incorrect order code.'
                ),
                PaycomException::ERROR_INVALID_ACCOUNT,
                'order_id'
            );
        }

        // initializing class properties
        $order = $this->find($params['account']);

        // check, whether an order is available or not in db
        if (!$order || !$order->id) {
            throw new PaycomException(
                $this->request_id,
                PaycomException::message(
                    'Неверный код заказа.',
                    'Harid kodida xatolik.',
                    'Incorrect order code.'
                ),
                PaycomException::ERROR_INVALID_ACCOUNT,
                'order_id'
            );
        }

        // check, whether order is available or not to sell
        if ($this->paid != self::STATE_AVAILABLE) {
            throw new PaycomException(
                $this->request_id,
                'Order state is not available.',
                PaycomException::ERROR_COULD_NOT_PERFORM
            );
        }

       // check, whether total_price in db and amount from client form correspond or not
        if ((100 * $this->total_price) != (1 * $params['amount'])) {
            throw new PaycomException(
                $this->request_id,
                'Incorrect amount.',
                PaycomException::ERROR_INVALID_AMOUNT
            );
        }

        // keep params for further use
        $this->params = $params;

        return true;
    }

    /**
     * Find order by given parameters.
     * @param mixed $params parameters.
     * @return Order|Order[] found order or array of orders.
     */
    public function find($params)
    {
        // todo: Implement searching order(s) by given parameters, populate current instance with data

        // Example implementation to load order by id
        if (isset($params['order_id'])) {

            $sql        = "select * from db_name.Order where id=:orderId";
            $sth        = self::db()->prepare($sql);
            $is_success = $sth->execute([':orderId' => $params['order_id']]);

            if ($is_success) {

                $row = $sth->fetch();

                if ($row) {

                    $this->id          = 1 * $row['id'];
                    $this->total_price      = 1 * $row['total_price'] + 1 * $row['delivery_price'];
                    $this->paid       = 1 * $row['paid'];

                    return $this;

                }

            }

        }

        return null;
    }

    /**
     * Change order's state to specified one.
     * @param int $state new state of the order
     * @return void
     */
    public function changeState($state)
    {
        // todo: Implement changing order state (reserve order after create transaction or free order after cancel)

        // Example implementation
        $this->paid = 1 * $state;
        $this->save();
    }

    /**
     * Check, whether order can be cancelled or not.
     * @return bool true - order is cancellable, otherwise false.
     */
    public function allowCancel()
    {
        // todo: Implement order cancelling allowance check

        // Example implementation
        return false; // do not allow cancellation
    }

    /**
     * Saves this order.
     * @throws PaycomException
     */
    public function save()
    {
        $db = self::db();

        if (!$this->id) {

            // If new order, set its state to waiting
            $this->paid = self::STATE_WAITING_PAY;

            // todo: Set customer ID
            // $this->user_id = 1 * SomeSessionManager::get('user_id');

            $sql        = "insert into db_name.Order set total_price = :pAmount, paid = :pState";
            $sth        = $db->prepare($sql);
            $is_success = $sth->execute([
                ':pAmount'  => $this->total_price,
                ':pState'   => $this->paid,
            ]);

            if ($is_success) {
                $this->id = $db->lastInsertId();
            }
        } else {

            $sql        = "update db_name.Order set paid = :pState where id = :pId";
            $sth        = $db->prepare($sql);
            $is_success = $sth->execute([':pState' => $this->paid, ':pId' => $this->id]);

        }

        if (!$is_success) {
            throw new PaycomException($this->request_id, 'Could not save order.', PaycomException::ERROR_INTERNAL_SYSTEM);
        }
    }
}