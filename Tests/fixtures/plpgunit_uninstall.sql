/********************************************************************************
The PostgreSQL License
Copyright (c) 2014, Binod Nepal, Mix Open Foundation (http://mixof.org).
Permission to use, copy, modify, and distribute this software and its documentation
for any purpose, without fee, and without a written agreement is hereby granted,
provided that the above copyright notice and this paragraph and
the following two paragraphs appear in all copies.
IN NO EVENT SHALL MIX OPEN FOUNDATION BE LIABLE TO ANY PARTY FOR DIRECT, INDIRECT,
SPECIAL, INCIDENTAL, OR CONSEQUENTIAL DAMAGES, INCLUDING LOST PROFITS,
ARISING OUT OF THE USE OF THIS SOFTWARE AND ITS DOCUMENTATION, EVEN IF
MIX OPEN FOUNDATION HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
MIX OPEN FOUNDATION SPECIFICALLY DISCLAIMS ANY WARRANTIES, INCLUDING,
BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE. THE SOFTWARE PROVIDED HEREUNDER IS ON AN "AS IS" BASIS,
AND MIX OPEN FOUNDATION HAS NO OBLIGATIONS TO PROVIDE MAINTENANCE, SUPPORT,
UPDATES, ENHANCEMENTS, OR MODIFICATIONS.
***********************************************************************************/
DROP TYPE IF EXISTS error CASCADE;
DROP SCHEMA IF EXISTS assert CASCADE;
DROP SCHEMA IF EXISTS unit_tests CASCADE;
DROP DOMAIN IF EXISTS public.test_result CASCADE;