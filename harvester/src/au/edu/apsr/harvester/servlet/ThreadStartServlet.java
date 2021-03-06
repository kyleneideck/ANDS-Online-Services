/**
 * Date Modified: $Date: 2009-08-18 12:43:25 +1000 (Tue, 18 Aug 2009) $
 * Version: $Revision: 84 $
 * 
 * Copyright 2008 The Australian National University (ANU)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package au.edu.apsr.harvester.servlet;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import au.edu.apsr.harvester.dao.DAOException;
import au.edu.apsr.harvester.thread.ThreadManager;
import au.edu.apsr.harvester.to.Harvest;
import au.edu.apsr.harvester.util.ServletSupport;

import org.apache.log4j.Logger;

/**
 * Servlet for starting a harvest.
 * 
 * Service parameters are described below. These are key/value
 * pairings in a URL, NOT Java args.
 * 
 * <p><strong>Service: startHarvest</strong>
 * <p><strong>Mandatory Parameters</strong>
 * <ul>
 * <li><strong>harvestid</strong> - 
 *          The harvestid of the harvest to be started</li></ul></p>
 * </p>
 * 
 * @author Scott Yeadon, APSR 
 */
public class ThreadStartServlet extends HttpServlet
{
    private final Logger log = Logger.getLogger(ThreadStartServlet.class);
    private ThreadManager threadManager = null;
    
    /**
     * obtain the ThreadManager
     * 
     * @exception ServletException
     */    
    public void init() throws ServletException
    {
        threadManager = ThreadManager.getThreadManager();
    }
    
    /**
     *  Process a GET request
     * 
     * @param request
     *          a HTTP request
     * 
     * @param response
     *          HTTP response
     * 
     * @throws ServletException
     */
    protected void doGet(final HttpServletRequest request,
                  final HttpServletResponse response) throws ServletException
    {
        doPost(request, response);
    }


    /**
     *  Process a POST request
     * 
     * @param request
     *          a HTTP request
     * 
     * @param response
     *          HTTP response
     * 
     * @throws ServletException
     */
    protected void doPost(final HttpServletRequest request,
                  final HttpServletResponse response) throws ServletException
    {
        String harvestID = (String)request.getParameter("harvestid");
        if (harvestID == null)
        {
            ServletSupport.doErrorResponse(response, "Missing parameter: harvestid");
            return;
        }
        doStop(response, harvestID);
    }
    
    
    /**
     * Start an existing harvest. The status is reported
     * as an XML response.
     * 
     * @param response
     *          HTTP response
     * 
     * @param harvestID
     *          The harvest ID of the harvest of interest
     * 
     * @throws ServletException
     */
    private void doStop(HttpServletResponse response,
                        String harvestID) throws ServletException
    {
        try
        {
            Harvest harvest = Harvest.find(harvestID);
            if (harvest == null)
            {
                log.error("No harvest record found for harvest id: " + harvestID);
                ServletSupport.doErrorResponse(response, "No harvest record found for harvest id: " + harvestID);
                return;
            }
            else
            {
                if (!threadManager.start(harvest))
                {
                    log.error("Failed to start harvest with id: " + harvestID);
                    ServletSupport.doErrorResponse(response, "Failed to start harvest with id: " + harvestID + ". Harvest may already be running");
                    return;
                }
                else
                {
                    log.info("Harvest " + harvestID + " started");
                    ServletSupport.doSuccessResponse(response, "Harvest " + harvestID + " started");
                }
            }
        }
        catch (DAOException daoe)
        {
            log.error("DAOException occurred", daoe);
            ServletSupport.doErrorResponse(response, daoe.getMessage());
        }
    }
}