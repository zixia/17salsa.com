<?php
/**
 * ECMALL: ����
 * ============================================================================
 * ��Ȩ���� (C) 2005-2008 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.shopex.cn
 * -------------------------------------------------------
 * �ⲻ��һ�������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�ʹ�ã�
 * ������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Id: inc.constant.php 6102 2008-11-21 04:52:37Z Garbin $
 */

if (!defined('IN_ECM'))
{
    trigger_error('Hacking attempt', E_USER_ERROR);
}


/* ϵͳ��Ϣ���� */

define('VERSION', '1.1 final');
define('RELEASE', '20081124');


define('IMG_AD',   0); // ͼƬ���
define('FALSH_AD', 1); // flash���
define('CODE_AD',  2); // ������
define('TEXT_AD',  3); // ���ֹ��

/* �ʼ������ȼ� */
define('MAIL_PRIORITY_LOW',     1);
define('MAIL_PRIORITY_MID',     2);
define('MAIL_PRIORITY_HIGH',    3);

/* �����ʼ���Э������ */
define('MAIL_PROTOCOL_LOCAL',       0, true);
define('MAIL_PROTOCOL_SMTP',        1, true);

/* ������� */
define('ACT_GROUPBUY',      1); // �Ź��
define('GROUPBUY_CANCEL',   1); //�Ź�ȡ��״̬

/* PAYLOG Type */

define('SHOPPING_ORDER', 1);    //����Ķ���

/* ����״̬���� */
define('ORDER_STATUS_TEMPORARY', 0);    //��ʱ
define('ORDER_STATUS_PENDING', 1);      //������
define('ORDER_STATUS_SUBMITTED', 2);    //���ύ
define('ORDER_STATUS_ACCEPTTED', 3);    //�ѽ���
define('ORDER_STATUS_PROCESSING', 4);   //������
define('ORDER_STATUS_SHIPPED', 5);      //�ѷ���
define('ORDER_STATUS_DELIVERED', 6);    //���ջ�
define('ORDER_STATUS_INVALID', 7);      //��Ч
define('ORDER_STATUS_REJECTED', 8);     //�Ѿܾ�

/* �������� */
define('ARC_HELP', 1); // ��վ����
define('ARC_NEWS', 2); // ��վ��Ѷ

/* �������۳��� */
define('ORDER_EVALUATION_UNEVALUATED', 0); //δ����
define('ORDER_EVALUATION_POOR', 1);        //����
define('ORDER_EVALUATION_COMMON', 2);       //����
define('ORDER_EVALUATION_GOOD', 3);         //����

/* ���������״̬ */
define('APPLY_RAW',     0); // δ����
define('APPLY_ACCEPT',  1); // �ѽ���
define('APPLY_DENY',    2); // ���ܾ�

/* ���̵�����ǰ����ʱ��������� */
define('STORE_RELET_TIME', 30 * 86400);

/* ��������״̬ */
//define('RELET_STATUS_')

?>
